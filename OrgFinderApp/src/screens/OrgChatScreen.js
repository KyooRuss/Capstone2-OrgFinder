import React, { useState, useEffect, useRef, useContext, useCallback } from 'react';
import {
    View, Text, FlatList, TextInput, TouchableOpacity,
    StyleSheet, KeyboardAvoidingView, Platform, ActivityIndicator,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { AuthContext } from '../context/AuthContext';
import api from '../api/client';

const POLL_INTERVAL = 3000;

export default function OrgChatScreen({ route, navigation }) {
    const { orgId, orgName } = route.params;
    const { user } = useContext(AuthContext);
    const insets = useSafeAreaInsets();

    const [messages, setMessages]   = useState([]);
    const [text, setText]           = useState('');
    const [loading, setLoading]     = useState(true);
    const [sending, setSending]     = useState(false);

    const lastIdRef   = useRef(0);
    const flatListRef = useRef(null);
    const seenIds     = useRef(new Set());

    const saveLastRead = useCallback(async (id) => {
        if (id > 0) await AsyncStorage.setItem(`chat_last_read_${orgId}`, String(id));
    }, [orgId]);

    const addMessages = useCallback((incoming) => {
        const fresh = incoming.filter(m => !seenIds.current.has(m.id));
        if (!fresh.length) return;
        fresh.forEach(m => seenIds.current.add(m.id));
        setMessages(prev => [...prev, ...fresh]);
        if (fresh.length) {
            const maxId = Math.max(...fresh.map(m => m.id));
            lastIdRef.current = Math.max(lastIdRef.current, maxId);
            saveLastRead(lastIdRef.current);
        }
    }, [saveLastRead]);

    // Initial load — last 50 messages
    useEffect(() => {
        let cancelled = false;
        api.get(`/chat/${orgId}/messages`)
            .then(res => {
                if (!cancelled) addMessages(res.data.messages);
            })
            .catch(() => {})
            .finally(() => { if (!cancelled) setLoading(false); });
        return () => { cancelled = true; };
    }, [orgId]);

    // Polling every 3 seconds
    useEffect(() => {
        const timer = setInterval(() => {
            api.get(`/chat/${orgId}/messages?after=${lastIdRef.current}`)
                .then(res => addMessages(res.data.messages))
                .catch(() => {});
        }, POLL_INTERVAL);
        return () => clearInterval(timer);
    }, [orgId]);

    // Scroll to bottom when messages change
    useEffect(() => {
        if (messages.length && flatListRef.current) {
            setTimeout(() => flatListRef.current?.scrollToEnd({ animated: true }), 100);
        }
    }, [messages.length]);

    const handleSend = async () => {
        const trimmed = text.trim();
        if (!trimmed || sending) return;
        setText('');
        setSending(true);
        try {
            const res = await api.post(`/chat/${orgId}/send`, { message: trimmed });
            addMessages([res.data.message]);
        } catch {}
        finally { setSending(false); }
    };

    const renderMessage = ({ item }) => {
        const mine = item.user_id === user?.id;
        return (
            <View style={[styles.msgRow, mine ? styles.msgRowMine : styles.msgRowOther]}>
                {!mine && (
                    <Text style={styles.senderName}>{item.sender_name}</Text>
                )}
                <View style={[styles.bubble, mine ? styles.bubbleMine : styles.bubbleOther]}>
                    <Text style={[styles.bubbleText, mine && styles.bubbleTextMine]}>
                        {item.message}
                    </Text>
                </View>
                <Text style={styles.timeText}>{item.time}</Text>
            </View>
        );
    };

    return (
        <KeyboardAvoidingView
            style={styles.root}
            behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
            keyboardVerticalOffset={0}
        >
            {/* Header */}
            <View style={[styles.header, { paddingTop: insets.top + 10 }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={22} color="#fff" />
                </TouchableOpacity>
                <View style={styles.headerInfo}>
                    <Text style={styles.headerTitle} numberOfLines={1}>{orgName}</Text>
                    <Text style={styles.headerSub}>Group Chat</Text>
                </View>
            </View>

            <View style={styles.flex}>
                {loading ? (
                    <View style={styles.center}>
                        <ActivityIndicator color="#4A6CF7" size="large" />
                    </View>
                ) : (
                    <FlatList
                        ref={flatListRef}
                        data={messages}
                        keyExtractor={item => String(item.id)}
                        renderItem={renderMessage}
                        contentContainerStyle={styles.list}
                        showsVerticalScrollIndicator={false}
                        onContentSizeChange={() => flatListRef.current?.scrollToEnd({ animated: false })}
                        ListEmptyComponent={
                            <View style={styles.empty}>
                                <Text style={styles.emptyIcon}>💬</Text>
                                <Text style={styles.emptyText}>No messages yet.</Text>
                                <Text style={styles.emptyHint}>Be the first to say something!</Text>
                            </View>
                        }
                    />
                )}

                {/* Input */}
                <View style={styles.inputRow}>
                    <TextInput
                        style={styles.input}
                        value={text}
                        onChangeText={setText}
                        placeholder="Type a message…"
                        placeholderTextColor="#b0bec5"
                        multiline
                        maxLength={1000}
                        returnKeyType="default"
                    />
                    <TouchableOpacity
                        style={[styles.sendBtn, (!text.trim() || sending) && styles.sendBtnDisabled]}
                        onPress={handleSend}
                        disabled={!text.trim() || sending}
                        activeOpacity={0.8}
                    >
                        {sending
                            ? <ActivityIndicator color="#fff" size="small" />
                            : <Ionicons name="send" size={18} color="#fff" />
                        }
                    </TouchableOpacity>
                </View>
            </View>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    root:  { flex: 1, backgroundColor: '#f0f2ff' },
    flex:  { flex: 1 },
    center: { flex: 1, alignItems: 'center', justifyContent: 'center' },

    // Header
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#4A6CF7',
        paddingHorizontal: 16,
        paddingBottom: 14,
        gap: 12,
    },
    backBtn: { padding: 4 },
    headerInfo: { flex: 1 },
    headerTitle: { fontSize: 16, fontWeight: '700', color: '#fff' },
    headerSub:   { fontSize: 12, color: 'rgba(255,255,255,0.7)', marginTop: 1 },

    // Messages
    list: { paddingHorizontal: 14, paddingVertical: 12, gap: 10 },

    msgRow:      { maxWidth: '78%' },
    msgRowMine:  { alignSelf: 'flex-end', alignItems: 'flex-end' },
    msgRowOther: { alignSelf: 'flex-start', alignItems: 'flex-start' },

    senderName: { fontSize: 11, fontWeight: '600', color: '#94a3b8', marginBottom: 3, paddingHorizontal: 4 },

    bubble:      { borderRadius: 16, paddingHorizontal: 14, paddingVertical: 9 },
    bubbleMine:  { backgroundColor: '#4A6CF7', borderBottomRightRadius: 4 },
    bubbleOther: { backgroundColor: '#fff', borderBottomLeftRadius: 4,
                   shadowColor: '#000', shadowOffset: { width: 0, height: 1 },
                   shadowOpacity: 0.06, shadowRadius: 4, elevation: 2 },

    bubbleText:     { fontSize: 14, color: '#1e2f6e', lineHeight: 20 },
    bubbleTextMine: { color: '#fff' },

    timeText: { fontSize: 10, color: '#b0bec5', marginTop: 3, paddingHorizontal: 4 },

    // Input
    inputRow: {
        flexDirection: 'row',
        alignItems: 'flex-end',
        gap: 10,
        paddingHorizontal: 14,
        paddingVertical: 10,
        backgroundColor: '#fff',
        borderTopWidth: 1,
        borderTopColor: '#f0f2ff',
    },
    input: {
        flex: 1,
        borderWidth: 1.5,
        borderColor: '#e2e8f0',
        borderRadius: 22,
        paddingHorizontal: 16,
        paddingVertical: 9,
        fontSize: 14,
        color: '#1e2f6e',
        maxHeight: 100,
        backgroundColor: '#f8faff',
    },
    sendBtn: {
        width: 42, height: 42,
        borderRadius: 21,
        backgroundColor: '#4A6CF7',
        alignItems: 'center', justifyContent: 'center',
    },
    sendBtnDisabled: { backgroundColor: '#c7d2fe' },

    // Empty
    empty: { alignItems: 'center', marginTop: 80 },
    emptyIcon: { fontSize: 40, marginBottom: 10 },
    emptyText: { fontSize: 15, fontWeight: '700', color: '#555' },
    emptyHint: { fontSize: 13, color: '#94a3b8', marginTop: 4 },
});

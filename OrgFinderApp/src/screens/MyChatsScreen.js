import React, { useState, useCallback } from 'react';
import {
    View, Text, FlatList, TouchableOpacity,
    StyleSheet, Image, ActivityIndicator,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api from '../api/client';

export default function MyChatsScreen({ navigation }) {
    const [chats, setChats]     = useState([]);
    const [loading, setLoading] = useState(true);
    const insets = useSafeAreaInsets();

    const load = useCallback(async () => {
        try {
            const keys = await AsyncStorage.getAllKeys();
            const chatKeys = keys.filter(k => k.startsWith('chat_last_read_'));
            const reads = {};
            if (chatKeys.length) {
                const pairs = await AsyncStorage.multiGet(chatKeys);
                pairs.forEach(([key, val]) => {
                    const orgId = key.replace('chat_last_read_', '');
                    reads[orgId] = val;
                });
            }
            const params = new URLSearchParams();
            Object.entries(reads).forEach(([orgId, lastId]) => {
                params.append(`reads[${orgId}]`, lastId);
            });
            const res = await api.get(`/chat/my-chats?${params.toString()}`);
            setChats(res.data.chats ?? []);
        } catch {}
        finally { setLoading(false); }
    }, []);

    useFocusEffect(useCallback(() => { load(); }, [load]));

    const renderChat = ({ item }) => (
        <TouchableOpacity
            style={styles.row}
            onPress={() => navigation.navigate('OrgChat', { orgId: item.org_id, orgName: item.org_name })}
            activeOpacity={0.8}
        >
            {/* Logo */}
            {item.org_logo
                ? <Image source={{ uri: item.org_logo }} style={styles.avatar} />
                : <View style={[styles.avatar, styles.avatarFallback]}>
                    <Text style={styles.avatarText}>{item.org_name?.[0] ?? 'O'}</Text>
                  </View>
            }

            {/* Info */}
            <View style={styles.info}>
                <Text style={styles.orgName} numberOfLines={1}>{item.org_name}</Text>
                <Text style={styles.lastMsg} numberOfLines={1}>
                    {item.last_message ?? 'No messages yet'}
                </Text>
            </View>

            {/* Right side */}
            <View style={styles.right}>
                {item.last_time ? (
                    <Text style={styles.time}>{item.last_time}</Text>
                ) : null}
                {item.unread > 0 && (
                    <View style={styles.badge}>
                        <Text style={styles.badgeText}>
                            {item.unread > 99 ? '99+' : item.unread}
                        </Text>
                    </View>
                )}
            </View>
        </TouchableOpacity>
    );

    return (
        <View style={styles.root}>
            <View style={[styles.header, { paddingTop: insets.top + 10 }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={22} color="#fff" />
                </TouchableOpacity>
                <Text style={styles.headerTitle}>My Chats</Text>
            </View>

            {loading ? (
                <View style={styles.center}>
                    <ActivityIndicator color="#4A6CF7" size="large" />
                </View>
            ) : (
                <FlatList
                    data={chats}
                    keyExtractor={item => String(item.org_id)}
                    renderItem={renderChat}
                    contentContainerStyle={chats.length === 0 && styles.emptyWrap}
                    ItemSeparatorComponent={() => <View style={styles.sep} />}
                    ListEmptyComponent={
                        <View style={styles.empty}>
                            <Ionicons name="chatbubbles-outline" size={56} color="#c7d2fe" />
                            <Text style={styles.emptyText}>No chats yet</Text>
                            <Text style={styles.emptyHint}>
                                Join an organization to access its group chat.
                            </Text>
                        </View>
                    }
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    root:   { flex: 1, backgroundColor: '#f0f2ff' },
    center: { flex: 1, alignItems: 'center', justifyContent: 'center' },

    header: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#4A6CF7',
        paddingHorizontal: 16,
        paddingBottom: 14,
        gap: 12,
    },
    backBtn:     { padding: 4 },
    headerTitle: { fontSize: 18, fontWeight: '700', color: '#fff' },

    row: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#fff',
        paddingHorizontal: 16,
        paddingVertical: 14,
        gap: 12,
    },
    sep: { height: 1, backgroundColor: '#f0f2ff', marginLeft: 80 },

    avatar:         { width: 52, height: 52, borderRadius: 26 },
    avatarFallback: { backgroundColor: '#4A6CF7', alignItems: 'center', justifyContent: 'center' },
    avatarText:     { color: '#fff', fontSize: 20, fontWeight: '700' },

    info:    { flex: 1 },
    orgName: { fontSize: 15, fontWeight: '700', color: '#1e2f6e', marginBottom: 3 },
    lastMsg: { fontSize: 13, color: '#94a3b8' },

    right:     { alignItems: 'flex-end', gap: 5 },
    time:      { fontSize: 11, color: '#b0bec5' },
    badge: {
        backgroundColor: '#4A6CF7',
        borderRadius: 10, minWidth: 20, height: 20,
        alignItems: 'center', justifyContent: 'center',
        paddingHorizontal: 5,
    },
    badgeText: { color: '#fff', fontSize: 11, fontWeight: '800' },

    emptyWrap: { flex: 1 },
    empty:     { flex: 1, alignItems: 'center', justifyContent: 'center', paddingTop: 80 },
    emptyText: { fontSize: 16, fontWeight: '700', color: '#94a3b8', marginTop: 14 },
    emptyHint: { fontSize: 13, color: '#c7d2fe', marginTop: 6, textAlign: 'center', paddingHorizontal: 40 },
});

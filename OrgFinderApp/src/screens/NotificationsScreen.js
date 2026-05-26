import React, { useState, useCallback } from 'react';
import {
    View, Text, FlatList, TouchableOpacity, StyleSheet,
    ActivityIndicator, RefreshControl,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import api from '../api/client';

const TYPE_META = {
    event_posted:        { icon: 'calendar-outline',         color: '#4A6CF7' },
    announcement:        { icon: 'megaphone-outline',        color: '#7c3aed' },
    membership_accepted: { icon: 'checkmark-circle-outline', color: '#16a34a' },
    membership_declined: { icon: 'close-circle-outline',     color: '#dc2626' },
};

// Transforms flat list → [{type:'header', label}, {type:'item', ...notification}, ...]
function buildRows(notifications) {
    const myOrg = notifications.filter(n => n.is_my_org);
    const other = notifications.filter(n => !n.is_my_org);
    const rows = [];

    if (myOrg.length > 0) {
        rows.push({ type: 'header', key: 'h1', label: 'Your Organization' });
        myOrg.forEach(n => rows.push({ type: 'item', key: `n${n.id}`, ...n }));
    }
    if (other.length > 0) {
        rows.push({ type: 'header', key: 'h2', label: 'Other Organizations' });
        other.forEach(n => rows.push({ type: 'item', key: `n${n.id}`, ...n }));
    }
    return rows;
}

export default function NotificationsScreen({ navigation }) {
    const [rows, setRows]         = useState([]);
    const [loading, setLoading]   = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [unread, setUnread]     = useState(0);

    const load = useCallback(async () => {
        try {
            const res = await api.get('/notifications');
            const notifs = res.data.notifications ?? [];
            setRows(buildRows(notifs));
            setUnread(res.data.unread_count ?? 0);
        } catch {}
        finally { setLoading(false); setRefreshing(false); }
    }, []);

    useFocusEffect(useCallback(() => { load(); }, [load]));

    const markRead = async (id) => {
        setRows(prev => prev.map(r => r.type === 'item' && r.id === id ? { ...r, is_read: true } : r));
        setUnread(prev => Math.max(0, prev - 1));
        try { await api.post(`/notifications/${id}/read`); } catch {}
    };

    const markAllRead = async () => {
        setRows(prev => prev.map(r => r.type === 'item' ? { ...r, is_read: true } : r));
        setUnread(0);
        try { await api.post('/notifications/read-all'); } catch {}
    };

    const handlePress = (item) => {
        if (!item.is_read) markRead(item.id);
        if (item.data?.event_id) {
            navigation.navigate('EventDetail', { id: item.data.event_id });
        }
    };

    const renderRow = ({ item }) => {
        if (item.type === 'header') {
            return <Text style={styles.sectionLabel}>{item.label}</Text>;
        }

        const meta = TYPE_META[item.type] ?? { icon: 'notifications-outline', color: '#4A6CF7' };

        return (
            <TouchableOpacity
                style={[styles.card, !item.is_read && styles.cardUnread]}
                onPress={() => handlePress(item)}
                activeOpacity={0.8}
            >
                <View style={[styles.iconWrap, { backgroundColor: meta.color + '18' }]}>
                    <Ionicons name={meta.icon} size={22} color={meta.color} />
                </View>

                <View style={styles.content}>
                    <Text style={[styles.title, !item.is_read && styles.titleUnread]}>
                        {item.title}
                    </Text>
                    <Text style={styles.body} numberOfLines={2}>{item.body}</Text>
                    <Text style={styles.time}>{item.created_at}</Text>
                </View>

                {!item.is_read && <View style={styles.dot} />}
            </TouchableOpacity>
        );
    };

    return (
        <View style={styles.root}>
            <LinearGradient
                colors={['#7CB9FF', '#4A6CF7']}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={styles.header}
            >
                <SafeAreaView>
                    <View style={styles.headerRow}>
                        <View>
                            <Text style={styles.headerTitle}>Notifications</Text>
                            {unread > 0 && (
                                <Text style={styles.headerSub}>{unread} unread</Text>
                            )}
                        </View>
                        {unread > 0 && (
                            <TouchableOpacity onPress={markAllRead} style={styles.readAllBtn}>
                                <Text style={styles.readAllText}>Mark all read</Text>
                            </TouchableOpacity>
                        )}
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {loading ? (
                <View style={styles.center}>
                    <ActivityIndicator color="#4A6CF7" size="large" />
                </View>
            ) : (
                <FlatList
                    data={rows}
                    keyExtractor={item => item.key}
                    renderItem={renderRow}
                    contentContainerStyle={[
                        styles.list,
                        rows.length === 0 && styles.emptyWrap,
                    ]}
                    showsVerticalScrollIndicator={false}
                    refreshControl={
                        <RefreshControl
                            refreshing={refreshing}
                            onRefresh={() => { setRefreshing(true); load(); }}
                            colors={['#4A6CF7']}
                        />
                    }
                    ListEmptyComponent={
                        <View style={styles.empty}>
                            <Ionicons name="notifications-outline" size={60} color="#c7d2fe" />
                            <Text style={styles.emptyTitle}>No notifications yet</Text>
                            <Text style={styles.emptyHint}>
                                You'll be notified about events and membership updates here.
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

    header: { paddingHorizontal: 16, paddingBottom: 20 },
    headerRow: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between' },
    headerTitle: { fontSize: 20, fontWeight: '700', color: '#fff', paddingTop: 8 },
    headerSub:   { fontSize: 13, color: 'rgba(255,255,255,0.8)', marginTop: 2 },

    readAllBtn:  { marginTop: 10, paddingHorizontal: 12, paddingVertical: 6, backgroundColor: 'rgba(255,255,255,0.2)', borderRadius: 10 },
    readAllText: { fontSize: 12, color: '#fff', fontWeight: '600' },

    list:     { paddingTop: 4, paddingBottom: 90 },
    emptyWrap:{ flex: 1 },

    sectionLabel: {
        fontSize: 11,
        fontWeight: '700',
        color: '#94a3b8',
        letterSpacing: 0.8,
        textTransform: 'uppercase',
        paddingHorizontal: 16,
        paddingTop: 16,
        paddingBottom: 6,
        backgroundColor: '#f0f2ff',
    },

    card: {
        flexDirection: 'row',
        alignItems: 'flex-start',
        backgroundColor: '#fff',
        paddingHorizontal: 16,
        paddingVertical: 14,
        gap: 12,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
    },
    cardUnread: { backgroundColor: '#f5f7ff' },

    iconWrap: { width: 44, height: 44, borderRadius: 22, alignItems: 'center', justifyContent: 'center', flexShrink: 0 },

    content:     { flex: 1, gap: 2 },
    title:       { fontSize: 14, fontWeight: '600', color: '#334155' },
    titleUnread: { color: '#1e2f6e', fontWeight: '700' },
    body:        { fontSize: 13, color: '#64748b', lineHeight: 18 },
    time:        { fontSize: 11, color: '#94a3b8', marginTop: 2 },

    dot: { width: 8, height: 8, borderRadius: 4, backgroundColor: '#4A6CF7', marginTop: 6, flexShrink: 0 },

    empty: {
        flex: 1,
        alignItems: 'center',
        justifyContent: 'center',
        paddingHorizontal: 40,
        gap: 12,
    },
    emptyTitle: { fontSize: 17, fontWeight: '700', color: '#64748b' },
    emptyHint:  { fontSize: 13, color: '#94a3b8', textAlign: 'center', lineHeight: 20 },
});

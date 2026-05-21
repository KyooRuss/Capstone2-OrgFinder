import React, { useState, useEffect, useContext, useCallback } from 'react';
import {
    View, Text, TouchableOpacity, StyleSheet,
    FlatList, Image, ActivityIndicator, RefreshControl,
    ScrollView, useWindowDimensions,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { AuthContext } from '../context/AuthContext';
import api from '../api/client';

const CHAR_HAPPY     = require('../../assets/character/happy.png');
const CHAR_SURPRISED = require('../../assets/character/surprised.png');

export default function HomeScreen({ navigation }) {
    const { user } = useContext(AuthContext);
    const [recs, setRecs]               = useState([]);
    const [recruiting, setRecruiting]   = useState([]);
    const [loading, setLoading]         = useState(true);
    const [refreshing, setRefreshing]   = useState(false);
    const [latestEvent, setLatestEvent] = useState(null);
    const [unreadCount, setUnreadCount] = useState(0);

    const { width } = useWindowDimensions();
    const mascotSize = Math.min(Math.round(width * 0.35), 140);

    const firstName = user?.first_name?.split(' ')[0] ?? 'Student';

    const loadData = useCallback(async () => {
        try {
            const [recRes, recruitRes] = await Promise.all([
                api.get('/recommendations'),
                api.get('/organizations/recruiting'),
            ]);
            setRecs(recRes.data.recommendations);
            setRecruiting(recruitRes.data.recruiting ?? []);
        } catch {}
        finally { setLoading(false); setRefreshing(false); }
    }, []);

    useEffect(() => { loadData(); }, []);

    const loadUnread = useCallback(async () => {
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
            const res = await api.get(`/chat/unread?${params.toString()}`);
            setUnreadCount(res.data.unread ?? 0);
        } catch {}
    }, []);

    useFocusEffect(useCallback(() => { loadUnread(); }, [loadUnread]));

    const getMatchColor = (pct) => {
        if (pct >= 70) return '#16a34a';
        if (pct >= 40) return '#d97706';
        return '#4A6CF7';
    };

    const renderOrg = ({ item, index }) => (
        <TouchableOpacity
            style={styles.orgCard}
            onPress={() => navigation.navigate('OrgDetail', { id: item.id })}
            activeOpacity={0.85}
        >
            {/* Rank badge */}
            <View style={styles.rankBadge}>
                <Text style={styles.rankText}>#{index + 1}</Text>
            </View>

            <View style={styles.cardTop}>
                {/* Logo */}
                <View style={styles.orgLogoWrap}>
                    {item.logo
                        ? <Image source={{ uri: item.logo }} style={styles.orgLogo} />
                        : <View style={[styles.orgLogo, styles.orgLogoFallback]}>
                            <Text style={styles.orgLogoText}>{item.name?.[0] ?? 'O'}</Text>
                          </View>
                    }
                </View>

                {/* Info */}
                <View style={styles.orgInfo}>
                    <Text style={styles.orgName} numberOfLines={2}>
                        {item.name}</Text>
                    {item.category ? (
                    <Text style={styles.categoryTag}>
                        {Array.isArray(item.category) ? item.category.join(', ') : item.category}
                    </Text>
                    ) : null}
                </View>
            </View>

            {/* Match reason */}
            {item.match_reason ? (
                <Text style={styles.matchReason} numberOfLines={1}>{item.match_reason}</Text>
            ) : null}
        </TouchableOpacity>
    );

   return (
        <View style={styles.root}>
            {/* Header */}
            <View style={styles.header}>
                {/* Gradient background — change height here independently */}
                <LinearGradient
                    colors={['#7CB9FF', '#4A6CF7']}
                    start={{ x: 0, y: 0 }}
                    end={{ x: 1, y: 1 }}
                    style={styles.headerGradient}
                />
                <SafeAreaView>
                    <View style={styles.headerRow}>
                        <View>
                            <Text style={styles.greeting}>Hello, {firstName}!</Text>
                        </View>
                        <View style={styles.headerActions}>
                            {/* Chat bell */}
                            <TouchableOpacity
                                style={styles.avatarBtn}
                                onPress={() => navigation.navigate('MyChats')}
                                activeOpacity={0.8}
                            >
                                <Ionicons name="chatbubble-ellipses" size={20} color="#fff" />
                                {unreadCount > 0 && (
                                    <View style={styles.badge}>
                                        <Text style={styles.badgeText}>
                                            {unreadCount > 99 ? '99+' : unreadCount}
                                        </Text>
                                    </View>
                                )}
                            </TouchableOpacity>
                            {/* Avatar */}
                            <TouchableOpacity
                                style={styles.avatarBtn}
                                onPress={() => navigation.navigate('Profile')}
                            >
                                <Text style={styles.avatarText}>{firstName?.[0] ?? 'S'}</Text>
                            </TouchableOpacity>
                        </View>
                    </View>

                    {/* Mascot */}
                    <View style={styles.mascotRow}>
                        <Image
                            source={latestEvent ? CHAR_SURPRISED : CHAR_HAPPY}
                            style={[styles.mascotImg, { width: mascotSize, height: mascotSize }]}
                            resizeMode="contain"
                        />
                        <View style={styles.bubble}>
                            <View style={styles.bubbleTail} />
                            {latestEvent ? (
                                <>
                                    <Text style={styles.bubbleText}>
                                        {latestEvent.organization.name} posted an upcoming event!
                                    </Text>
                                    <TouchableOpacity onPress={() => navigation.navigate('EventDetail', { id: latestEvent.id })}>
                                        <Text style={styles.bubbleLink}>View details</Text>
                                    </TouchableOpacity>
                                </>
                            ) : (
                                <View>
                                    <Text style={styles.characterName}>Hami</Text>
                                    <Text style={styles.bubbleText}>Welcome! What are we exploring today?</Text>
                                </View>
                            )}
                        </View>
                    </View>

                </SafeAreaView>
            </View>

            {/* Quick actions — floating card between header and body */}
            <View style={styles.quickActions}>
                <TouchableOpacity style={styles.quickBtn} onPress={() => navigation.navigate('Events')}>
                    <View style={styles.quickIconWrap}>
                        <Ionicons name="calendar" size={22} color="#4A6CF7" />
                    </View>
                    <Text style={styles.quickLabel}>Upcoming{'\n'}Events</Text>
                </TouchableOpacity>
                <View style={styles.divider} />
                <TouchableOpacity style={styles.quickBtn} onPress={() => navigation.navigate('Orgs')}>
                    <View style={styles.quickIconWrap}>
                        <Ionicons name="business" size={22} color="#4A6CF7" />
                    </View>
                    <Text style={styles.quickLabel}>Explore{'\n'}Organizations</Text>
                </TouchableOpacity>
            </View>

            {/* Recommendations */}
            <View style={styles.body}>
                {loading ? (
                    <ActivityIndicator style={{ marginTop: 40 }} color="#4A6CF7" size="large" />
                ) : (
                    <FlatList
                        data={recs}
                        keyExtractor={item => String(item.id)}
                        renderItem={renderOrg}
                        contentContainerStyle={styles.list}
                        showsVerticalScrollIndicator={false}
                        refreshControl={
                            <RefreshControl
                                refreshing={refreshing}
                                onRefresh={() => { setRefreshing(true); loadData(); }}
                                colors={['#4A6CF7']}
                            />
                        }
                        ListHeaderComponent={
                            <>
                                {recruiting.length > 0 && (
                                    <View style={styles.recruitSection}>
                                        <View style={styles.recruitSectionHeader}>
                                            <Text style={styles.recruitTitle}>Now Recruiting</Text>
                                            <Text style={styles.recruitSub}>Organizations open for Membership</Text>
                                        </View>
                                        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.recruitList}>
                                            {recruiting.map(org => (
                                                <TouchableOpacity
                                                    key={org.id}
                                                    style={styles.recruitItem}
                                                    onPress={() => navigation.navigate('OrgDetail', { id: org.id })}
                                                    activeOpacity={0.75}
                                                >
                                                    {org.logo
                                                        ? <Image source={{ uri: org.logo }} style={styles.recruitAvatar} />
                                                        : <View style={[styles.recruitAvatar, styles.recruitAvatarFallback]}>
                                                            <Text style={styles.recruitAvatarText}>{org.name?.[0] ?? 'O'}</Text>
                                                          </View>
                                                    }
                                                    <Text style={styles.recruitName} numberOfLines={2}>{org.name}</Text>
                                                </TouchableOpacity>
                                            ))}
                                        </ScrollView>
                                    </View>
                                )}
                                <View style={styles.recHeader}>
                                    <Text style={styles.recTitle}>Recommended Orgs</Text>
                                    <Text style={styles.recSub}>Based on your interests & hobbies</Text>
                                </View>
                            </>
                        }
                        ListEmptyComponent={
                            <View style={styles.empty}>
                                <Text style={styles.emptyIcon}>🔍</Text>
                                <Text style={styles.emptyText}>No recommendations yet.</Text>
                                <Text style={styles.emptyHint}>Complete your profile to get personalized suggestions.</Text>
                                <TouchableOpacity onPress={() => navigation.navigate('Orgs')} style={styles.exploreBtn}>
                                    <Text style={styles.exploreBtnText}>Explore All Organizations</Text>
                                </TouchableOpacity>
                            </View>
                        }
                    />
                )}
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#f0f2ff' },
    // Header
    header: { paddingHorizontal: 20, paddingBottom: 70 },
    headerGradient: {
        position: 'absolute',
        top: 0, left: 0, right: 0,
        height: 260,           
    },
    headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 10 },
    greeting: { fontSize: 22, fontWeight: '600', color: '#fff' },
    subGreeting: { fontSize: 13, color: 'rgba(255,255,255,0.75)', marginTop: 2 },
    headerActions: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    avatarBtn: {
        width: 40, height: 40, borderRadius: 20,
        backgroundColor: 'rgba(255,255,255,0.25)',
        alignItems: 'center', justifyContent: 'center',
    },
    avatarText: { color: '#fff', fontWeight: '700', fontSize: 16 },
    badge: {
        position: 'absolute', top: -4, right: -4,
        backgroundColor: '#ef4444',
        borderRadius: 10, minWidth: 18, height: 18,
        alignItems: 'center', justifyContent: 'center',
        paddingHorizontal: 4,
        borderWidth: 1.5, borderColor: '#4A6CF7',
    },
    badgeText: { color: '#fff', fontSize: 10, fontWeight: '800' },
    quickActions: {
        flexDirection: 'row',
        backgroundColor: '#fff',
        borderRadius: 18,
        marginHorizontal: 20,
        marginTop: -70,
        paddingVertical: 16,
        paddingHorizontal: 20,
        shadowColor: '#4A6CF7',
        shadowOffset: { width: 0, height: 6 },
        shadowOpacity: 0.12,
        shadowRadius: 14,
        elevation: 8,
        zIndex: 10,
    },
    quickBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 10 },
    quickIconWrap: {
        width: 40, height: 40, borderRadius: 12,
        backgroundColor: '#eff3ff',
        alignItems: 'center', justifyContent: 'center',
    },
    quickLabel: { color: '#1e2f6e', fontSize: 13, fontWeight: '600', lineHeight: 18 },
    divider: { width: 1, backgroundColor: '#e8e8e8', marginHorizontal: 16 },

    // Character
    mascotRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: 12,
        marginBottom: 0,
    },
    mascotImg: { width: 110, height: 110 },
    bubble: {
        flex: 1,
        backgroundColor: 'rgba(255,255,255,0.2)',
        borderRadius: 12,
        padding: 12,
        marginLeft: 4,
    },
    bubbleTail: {
        position: 'absolute',
        left: -8,
        top: 14,
        borderTopWidth: 7,
        borderBottomWidth: 7,
        borderRightWidth: 8,
        borderTopColor: 'transparent',
        borderBottomColor: 'transparent',
        borderRightColor: 'rgba(255,255,255,0.2)',
    },
    characterName: {
        fontSize: 11,
        color: '#C2F2FF',
        fontWeight: '800',
        lineHeight: 20,
    },
    bubbleText: {
        fontSize: 14,
        color: '#fff',
        fontWeight: '500',
        lineHeight: 19,
    },
    bubbleLink: {
        fontSize: 12,
        color: '#fff',
        fontWeight: '700',
        marginTop: 6,
        textDecorationLine: 'underline',
    },

    // Now Recruiting
    recruitSection: { marginBottom: 4 },
    recruitSectionHeader: { paddingHorizontal: 20, paddingTop: 20, paddingBottom: 8 },
    recruitTitle: { fontSize: 18, fontWeight: '800', color: '#1e2f6e' },
    recruitSub: { fontSize: 12, color: '#94a3b8', marginTop: 2 },
    recruitList: { paddingHorizontal: 16, paddingBottom: 4, gap: 16 },
    recruitItem: { alignItems: 'center', width: 64 },
    recruitAvatar: { width: 60, height: 60, borderRadius: 30, marginBottom: 6 },
    recruitAvatarFallback: { backgroundColor: '#4A6CF7', alignItems: 'center', justifyContent: 'center' },
    recruitAvatarText: { color: '#fff', fontSize: 22, fontWeight: '700' },
    recruitName: { fontSize: 11, fontWeight: '600', color: '#1e2f6e', textAlign: 'center' },

    // Body
    body: { flex: 1 },
    recHeader: { paddingHorizontal: 20, paddingTop: 20, paddingBottom: 8 },
    recTitle: { fontSize: 18, fontWeight: '800', color: '#1e2f6e' },
    recSub: { fontSize: 12, color: '#94a3b8', marginTop: 2 },
    list: { paddingHorizontal: 16, paddingBottom: 30, gap: 12 },

    // Org card
    orgCard: {
        backgroundColor: '#fff',
        borderRadius: 16,
        padding: 14,
        shadowColor: '#4A6CF7',
        shadowOffset: { width: 0, height: 3 },
        shadowOpacity: 0.08,
        shadowRadius: 10,
        elevation: 3,
    },
    rankBadge: {
        position: 'absolute', top: 10, right: 10,
        backgroundColor: '#f0f2ff',
        borderRadius: 8, paddingHorizontal: 7, paddingVertical: 3,
    },
    rankText: { fontSize: 11, fontWeight: '700', color: '#4A6CF7' },
    cardTop: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
    orgLogoWrap: { marginRight: 12 },
    orgLogo: { width: 54, height: 54, borderRadius: 27 },
    orgLogoFallback: {
        backgroundColor: '#4A6CF7',
        alignItems: 'center', justifyContent: 'center',
    },
    orgLogoText: { color: '#fff', fontSize: 20, fontWeight: '700' },
    orgInfo: { flex: 1 },
    orgName: { fontSize: 15, fontWeight: '700', color: '#1e2f6e', marginBottom: 4, paddingRight: 50 },
    categoryTag: {
        alignSelf: 'flex-start',
        backgroundColor: '#eff6ff', borderRadius: 6,
        paddingHorizontal: 8, paddingVertical: 2,
        fontSize: 11, color: '#4A6CF7', fontWeight: '600',
    },

    // Match badge
    matchBadge: {
        borderWidth: 1.5, borderRadius: 10,
        paddingHorizontal: 8, paddingVertical: 4,
        alignItems: 'center', minWidth: 52,
    },
    matchPct: { fontSize: 15, fontWeight: '800' },
    matchLabel: { fontSize: 9, fontWeight: '600', textTransform: 'uppercase' },

    // Match reason & tags
    matchReason: { fontSize: 12, color: '#64748b', marginBottom: 8 },
    tagsRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
    tag: {
        backgroundColor: '#f0fdf4', borderRadius: 20,
        paddingHorizontal: 10, paddingVertical: 4,
        borderWidth: 1, borderColor: '#bbf7d0',
    },
    tagText: { fontSize: 11, color: '#16a34a', fontWeight: '600' },

    // Empty
    empty: { alignItems: 'center', marginTop: 60, paddingHorizontal: 30 },
    emptyIcon: { fontSize: 40, marginBottom: 12 },
    emptyText: { fontSize: 16, fontWeight: '700', color: '#555' },
    emptyHint: { fontSize: 13, color: '#888', marginTop: 6, marginBottom: 20, textAlign: 'center' },
    exploreBtn: {
        backgroundColor: '#4A6CF7', borderRadius: 24,
        paddingHorizontal: 24, paddingVertical: 12,
    },
    exploreBtnText: { color: '#fff', fontWeight: '600', fontSize: 14 },
});

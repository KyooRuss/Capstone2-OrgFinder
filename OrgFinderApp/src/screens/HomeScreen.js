import React, { useState, useEffect, useContext, useCallback } from 'react';
import {
    View, Text, TouchableOpacity, StyleSheet,
    Image, ActivityIndicator, RefreshControl,
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

const POSITION_RESPONSIBILITIES = {
    president:      'Lead with integrity — set the vision, guide your team, and represent the organization with pride.',
    vice_president: 'Support the President, coordinate officers, and step up whenever the organization needs you.',
    secretary:      'Keep records accurate, manage communications, and make sure nothing important slips through.',
    adviser:        'Guide and mentor members with wisdom, uphold standards, and bridge students and administration.',
    officer:        'Own your tasks, coordinate with your team, and deliver results that reflect well on the organization.',
    member:         'Participate actively, follow your leaders\' guidance, and uphold the values of your organization.',
};

const MOTIVATIONAL_QUOTES = [
    'Great things are built by people who show up every single day.',
    'Your contribution matters — even the smallest effort drives the team forward.',
    'Leadership is not a position. It is a choice to make a difference.',
    'One step at a time. Consistency beats perfection every time.',
    'The strength of the team is each individual member. The strength of each member is the team.',
    'Discipline is the bridge between goals and accomplishment.',
    'Show up, work hard, and inspire others to do the same.',
    'Excellence is not a skill — it is an attitude.',
    'Together, we can achieve what none of us could alone.',
    'Your role in this organization is valuable. Never underestimate it.',
    'Serve with purpose. Lead with heart. Act with integrity.',
    'Be the reason your organization succeeds today.',
    'Small acts of dedication create big moments of success.',
    'Your organization needs the best version of you — give it freely.',
    'True leaders lift others up as they rise.',
];

export default function HomeScreen({ navigation }) {
    const { user, logout } = useContext(AuthContext);
    const isProf   = user?.role === 'prof';

    const [myOrgs, setMyOrgs]               = useState([]);
    const [announcements, setAnnouncements] = useState([]);
    const [recommended, setRecommended]     = useState([]);
    const [loading, setLoading]             = useState(true);
    const [refreshing, setRefreshing]       = useState(false);
    const [unreadCount, setUnreadCount]     = useState(0);

    const { width } = useWindowDimensions();
    const isSmall    = width < 380;
    const mascotSize = Math.min(Math.round(width * 0.33), 130);
    const greetSize  = isSmall ? 17 : width < 414 ? 19 : 22;
    const iconSize   = isSmall ? 34 : 38;
    const hPad       = isSmall ? 14 : 18;

    const firstName   = user?.first_name?.split(' ')[0] ?? 'there';
    const greetingName = isProf
        ? `Prof. ${user?.last_name ?? ''}`.trim()
        : firstName;

    const loadData = useCallback(async () => {
        try {
            const [eventsRes, orgsRes, recRes] = await Promise.all([
                api.get('/events/upcoming'),
                isProf ? Promise.resolve({ data: { organizations: [] } }) : api.get('/organizations/my'),
                isProf ? Promise.resolve({ data: { recommendations: [] } }) : api.get('/recommendations').catch(() => ({ data: { recommendations: [] } })),
            ]);
            setAnnouncements((eventsRes.data.events ?? []).slice(0, 3));
            setMyOrgs(orgsRes.data.organizations ?? []);
            setRecommended(recRes.data.recommendations ?? []);
        } catch {}
        finally { setLoading(false); setRefreshing(false); }
    }, [isProf]);

    useFocusEffect(useCallback(() => { loadData(); }, [loadData]));

    const loadUnread = useCallback(async () => {
        try {
            const keys     = await AsyncStorage.getAllKeys();
            const chatKeys = keys.filter(k => k.startsWith('chat_last_read_'));
            const reads    = {};
            if (chatKeys.length) {
                const pairs = await AsyncStorage.multiGet(chatKeys);
                pairs.forEach(([key, val]) => {
                    reads[key.replace('chat_last_read_', '')] = val;
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

    const bubbleText = () => {
        if (isProf) {
            if (announcements.length > 0) return 'Here are the latest events happening in school!';
            return 'Stay updated with school organizations and events!';
        }
        if (announcements.length > 0) return 'Check out the latest events from your organizations!';
        return 'Welcome! What are we exploring today?';
    };

    return (
        <View style={styles.root}>
            {/* Gradient header */}
            <View style={[styles.headerWrap, { paddingHorizontal: hPad, paddingBottom: isSmall ? 12 : 16 }]}>
                <LinearGradient
                    colors={['#7CB9FF', '#4A6CF7']}
                    start={{ x: 0, y: 0 }}
                    end={{ x: 1, y: 1 }}
                    style={StyleSheet.absoluteFill}
                />
                <SafeAreaView>
                    <View style={[styles.headerRow, { paddingTop: isSmall ? 6 : 10 }]}>
                        <Text
                            style={[styles.greeting, { fontSize: greetSize }]}
                            numberOfLines={1}
                            adjustsFontSizeToFit
                        >
                            Hello, {greetingName}!
                        </Text>
                        <View style={styles.headerActions}>
                            {!isProf && (
                                <TouchableOpacity
                                    style={[styles.iconBtn, { width: iconSize, height: iconSize, borderRadius: iconSize / 2 }]}
                                    onPress={() => navigation.navigate('MyChats')}
                                    activeOpacity={0.8}
                                >
                                    <Ionicons name="chatbubble-ellipses" size={isSmall ? 17 : 19} color="#fff" />
                                    {unreadCount > 0 && (
                                        <View style={styles.badge}>
                                            <Text style={styles.badgeText}>
                                                {unreadCount > 99 ? '99+' : unreadCount}
                                            </Text>
                                        </View>
                                    )}
                                </TouchableOpacity>
                            )}
                            {isProf ? (
                                <TouchableOpacity
                                    style={[styles.iconBtn, { width: iconSize, height: iconSize, borderRadius: iconSize / 2 }]}
                                    onPress={logout}
                                    activeOpacity={0.8}
                                >
                                    <Ionicons name="log-out-outline" size={isSmall ? 18 : 20} color="#fff" />
                                </TouchableOpacity>
                            ) : (
                                <TouchableOpacity
                                    style={[styles.iconBtn, { width: iconSize, height: iconSize, borderRadius: iconSize / 2 }]}
                                    onPress={() => navigation.navigate('Profile')}
                                    activeOpacity={0.8}
                                >
                                    {user?.profile_photo
                                        ? <Image source={{ uri: user.profile_photo }} style={[styles.avatarImg, { width: iconSize, height: iconSize, borderRadius: iconSize / 2 }]} />
                                        : <Text style={[styles.avatarText, { fontSize: isSmall ? 13 : 15 }]}>{firstName?.[0] ?? 'U'}</Text>
                                    }
                                </TouchableOpacity>
                            )}
                        </View>
                    </View>

                    {/* Mascot row */}
                    <View style={[styles.mascotRow, { marginTop: isSmall ? 8 : 12 }]}>
                        <Image
                            source={announcements.length ? CHAR_SURPRISED : CHAR_HAPPY}
                            style={[styles.mascotImg, { width: mascotSize, height: mascotSize }]}
                            resizeMode="contain"
                        />
                        <View style={styles.bubble}>
                            <View style={styles.bubbleTail} />
                            <Text style={styles.characterName}>Hami</Text>
                            <Text style={styles.bubbleText}>{bubbleText()}</Text>
                        </View>
                    </View>
                </SafeAreaView>
            </View>

            {/* Body */}
            {loading ? (
                <View style={styles.center}>
                    <ActivityIndicator color="#4A6CF7" size="large" />
                </View>
            ) : (
                <ScrollView
                    showsVerticalScrollIndicator={false}
                    contentContainerStyle={styles.listContent}
                    refreshControl={
                        <RefreshControl
                            refreshing={refreshing}
                            onRefresh={() => { setRefreshing(true); loadData(); }}
                            colors={['#4A6CF7']}
                        />
                    }
                >
                    {/* Daily Reminder (students with org membership only) */}
                    {!isProf && myOrgs.length > 0 && (() => {
                        const rawPos = myOrgs[0]?.position ?? 'member';
                        const posKey = rawPos.toLowerCase().replace(/[\s-]+/g, '_');
                        const text   = POSITION_RESPONSIBILITIES[posKey] ?? POSITION_RESPONSIBILITIES.member;
                        const quote  = MOTIVATIONAL_QUOTES[new Date().getDate() % MOTIVATIONAL_QUOTES.length];
                        return (
                            <View style={[styles.section, { paddingBottom: 4 }]}>
                                <View style={styles.reminderCard}>
                                    <Text style={styles.reminderText}>{text}</Text>
                                    <View style={styles.reminderDivider} />
                                    <Text style={styles.quoteText}>{quote}</Text>
                                </View>
                            </View>
                        );
                    })()}

                    {/* Announcements (upcoming events) */}
                    {announcements.length > 0 && (
                        <View style={styles.section}>
                            <Text style={styles.sectionTitle}>Announcements</Text>
                            <Text style={styles.sectionSub}>Stay up-to-date with latest events and activities</Text>
                            {announcements.map(item => (
                                <TouchableOpacity
                                    key={item.id}
                                    style={styles.announcementCard}
                                    onPress={() => navigation.navigate('EventDetail', { id: item.id })}
                                    activeOpacity={0.85}
                                >
                                    {item.poster
                                        ? <Image source={{ uri: item.poster }} style={styles.announcementPoster} />
                                        : <View style={[styles.announcementPoster, styles.announcementPosterFallback]}>
                                            <Ionicons name="calendar" size={20} color="#c7d2fe" />
                                          </View>
                                    }
                                    <View style={styles.announcementInfo}>
                                        <Text style={styles.announcementOrg} numberOfLines={1}>
                                            {item.organization?.name}
                                        </Text>
                                        <Text style={styles.announcementTitle} numberOfLines={2}>
                                            {item.title}
                                        </Text>
                                        <Text style={styles.announcementDate}>{item.date}</Text>
                                    </View>
                                    <Ionicons name="chevron-forward" size={18} color="#c7d2fe" />
                                </TouchableOpacity>
                            ))}
                        </View>
                    )}

                    {/* Recommended orgs — only for students with no membership yet */}
                    {!isProf && myOrgs.length === 0 && recommended.length > 0 && (
                        <View style={styles.section}>
                            <Text style={styles.sectionTitle}>Recommended For You</Text>
                            <Text style={styles.sectionSub}>Based on your profile and interests</Text>
                            {recommended.slice(0, 5).map(item => (
                                <TouchableOpacity
                                    key={item.id}
                                    style={styles.recCard}
                                    onPress={() => navigation.navigate('OrgDetail', { id: item.id })}
                                    activeOpacity={0.85}
                                >
                                    {item.logo
                                        ? <Image source={{ uri: item.logo }} style={styles.recLogo} />
                                        : <View style={[styles.recLogo, styles.orgLogoFallback]}>
                                            <Text style={styles.orgLogoText}>{item.name?.[0] ?? 'O'}</Text>
                                          </View>
                                    }
                                    <View style={styles.recInfo}>
                                        <Text style={styles.recName} numberOfLines={1}>{item.name}</Text>
                                        <Text style={styles.recReason} numberOfLines={2}>{item.match_reason}</Text>
                                    </View>
                                    <View style={styles.recRight}>
                                        <View style={styles.matchBadge}>
                                            <Text style={styles.matchPct}>{item.match_pct}%</Text>
                                        </View>
                                        {item.is_recruiting && (
                                            <View style={styles.openBadge}>
                                                <Text style={styles.openText}>Open</Text>
                                            </View>
                                        )}
                                    </View>
                                </TouchableOpacity>
                            ))}
                        </View>
                    )}

                    {/* My Organizations (students only) */}
                    {!isProf && (
                        <View style={styles.section}>
                            <Text style={styles.sectionTitle}>My Organizations</Text>
                            <Text style={styles.sectionSub}>
                                {myOrgs.length > 0 ? 'Your active memberships' : 'You have not joined any organization yet'}
                            </Text>

                            {myOrgs.length > 0 ? myOrgs.map(org => (
                                <View key={org.id} style={styles.orgCard}>
                                    {/* Logo + info */}
                                    <TouchableOpacity
                                        style={styles.orgCardLeft}
                                        onPress={() => navigation.navigate('OrgDetail', { id: org.id })}
                                        activeOpacity={0.85}
                                    >
                                        {org.logo
                                            ? <Image source={{ uri: org.logo }} style={styles.orgLogo} />
                                            : <View style={[styles.orgLogo, styles.orgLogoFallback]}>
                                                <Text style={styles.orgLogoText}>{org.name?.[0] ?? 'O'}</Text>
                                              </View>
                                        }
                                        <View style={styles.orgInfo}>
                                            <Text style={styles.orgName} numberOfLines={1}>{org.name}</Text>
                                            {org.position && (
                                                <Text style={styles.orgPosition}>{org.position}</Text>
                                            )}
                                        </View>
                                    </TouchableOpacity>

                                    <Ionicons name="chevron-forward" size={18} color="#c7d2fe" />
                                </View>
                            )) : (
                                <TouchableOpacity
                                    style={styles.joinNudge}
                                    onPress={() => navigation.navigate('Recruitment')}
                                    activeOpacity={0.85}
                                >
                                    <Ionicons name="add-circle-outline" size={20} color="#4A6CF7" />
                                    <Text style={styles.joinNudgeText}>Browse open recruitments</Text>
                                    <Ionicons name="chevron-forward" size={16} color="#c7d2fe" />
                                </TouchableOpacity>
                            )}
                        </View>
                    )}
                </ScrollView>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    root:   { flex: 1, backgroundColor: '#f0f2ff' },
    center: { flex: 1, alignItems: 'center', justifyContent: 'center' },

    // Header
    headerWrap: { overflow: 'hidden' },
    headerRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    greeting: { fontWeight: '700', color: '#fff', flex: 1, marginRight: 10 },
    headerActions: { flexDirection: 'row', gap: 8 },
    iconBtn: {
        backgroundColor: 'rgba(255,255,255,0.25)',
        alignItems: 'center', justifyContent: 'center',
    },
    avatarImg:  { borderRadius: 20 },
    avatarText: { color: '#fff', fontWeight: '700' },
    badge: {
        position: 'absolute', top: -4, right: -4,
        backgroundColor: '#ef4444',
        borderRadius: 10, minWidth: 18, height: 18,
        alignItems: 'center', justifyContent: 'center',
        paddingHorizontal: 4,
        borderWidth: 1.5, borderColor: '#4A6CF7',
    },
    badgeText: { color: '#fff', fontSize: 10, fontWeight: '800' },

    // Mascot
    mascotRow: { flexDirection: 'row', alignItems: 'center' },
    mascotImg: {},
    bubble: {
        flex: 1,
        backgroundColor: 'rgba(255,255,255,0.2)',
        borderRadius: 12,
        padding: 12,
        marginLeft: 4,
    },
    bubbleTail: {
        position: 'absolute', left: -8, top: 14,
        borderTopWidth: 7, borderBottomWidth: 7, borderRightWidth: 8,
        borderTopColor: 'transparent', borderBottomColor: 'transparent',
        borderRightColor: 'rgba(255,255,255,0.2)',
    },
    characterName: { fontSize: 11, color: '#C2F2FF', fontWeight: '800', lineHeight: 18 },
    bubbleText:    { fontSize: 13, color: '#fff', fontWeight: '500', lineHeight: 19 },

    // List
    listContent: { paddingBottom: 90 },

    // Sections
    section:      { paddingTop: 20, paddingHorizontal: 16 },
    sectionTitle: { fontSize: 18, fontWeight: '800', color: '#1e2f6e' },
    sectionSub:   { fontSize: 12, color: '#94a3b8', marginTop: 2, marginBottom: 10 },

    // Announcement card
    announcementCard: {
        flexDirection: 'row', alignItems: 'center',
        backgroundColor: '#fff', borderRadius: 14,
        padding: 12, marginBottom: 10, gap: 12,
        shadowColor: '#4A6CF7', shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.07, shadowRadius: 8, elevation: 2,
    },
    announcementPoster: { width: 48, height: 48, borderRadius: 10 },
    announcementPosterFallback: {
        backgroundColor: '#eff3ff',
        alignItems: 'center', justifyContent: 'center',
    },
    announcementInfo:  { flex: 1 },
    announcementOrg:   { fontSize: 11, fontWeight: '700', color: '#4A6CF7', marginBottom: 2 },
    announcementTitle: { fontSize: 13, fontWeight: '600', color: '#1e2f6e', lineHeight: 18 },
    announcementDate:  { fontSize: 11, color: '#94a3b8', marginTop: 3 },

    // My Organizations card
    orgCard: {
        backgroundColor: '#fff', borderRadius: 16,
        flexDirection: 'row', alignItems: 'center',
        paddingVertical: 12, paddingHorizontal: 14,
        marginBottom: 10, gap: 10,
        shadowColor: '#4A6CF7', shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.07, shadowRadius: 8, elevation: 2,
    },
    orgCardLeft: { flexDirection: 'row', alignItems: 'center', flex: 1, gap: 12 },
    orgLogo:     { width: 46, height: 46, borderRadius: 23 },
    orgLogoFallback: { backgroundColor: '#4A6CF7', alignItems: 'center', justifyContent: 'center' },
    orgLogoText: { color: '#fff', fontSize: 18, fontWeight: '700' },
    orgInfo:     { flex: 1 },
    orgName:     { fontSize: 14, fontWeight: '700', color: '#1e2f6e' },
    orgPosition: { fontSize: 11, color: '#4A6CF7', fontWeight: '600', marginTop: 2 },


    // Daily reminder card
    reminderCard: {
        backgroundColor: '#fff',
        borderRadius: 16,
        padding: 16,
        borderLeftWidth: 4,
        borderLeftColor: '#4A6CF7',
        shadowColor: '#4A6CF7',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.09,
        shadowRadius: 10,
        elevation: 3,
    },
    reminderHeader: {
        flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 10,
    },
    reminderBadge: {
        width: 28, height: 28, borderRadius: 14,
        backgroundColor: '#4A6CF7',
        alignItems: 'center', justifyContent: 'center',
    },
    reminderPosition: { fontSize: 13, fontWeight: '700', color: '#4A6CF7' },
    reminderText:     { fontSize: 13, color: '#334155', lineHeight: 21 },
    reminderDivider:  { height: 1, backgroundColor: '#e2e8f0', marginVertical: 12 },
    quoteRow:         { flexDirection: 'row', alignItems: 'flex-start', gap: 6 },
    quoteText:        { flex: 1, fontSize: 12, color: '#64748b', fontStyle: 'italic', lineHeight: 18 },

    // Recommendation cards (new users)
    recCard: {
        flexDirection: 'row', alignItems: 'center', gap: 12,
        backgroundColor: '#fff', borderRadius: 14,
        padding: 12, marginBottom: 10,
        shadowColor: '#4A6CF7', shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.08, shadowRadius: 8, elevation: 2,
    },
    recLogo:   { width: 46, height: 46, borderRadius: 23 },
    recInfo:   { flex: 1 },
    recName:   { fontSize: 14, fontWeight: '700', color: '#1e2f6e' },
    recReason: { fontSize: 11, color: '#64748b', marginTop: 3, lineHeight: 15 },
    recRight:  { alignItems: 'flex-end', gap: 5 },
    matchBadge: {
        backgroundColor: '#eff3ff', borderRadius: 8,
        paddingHorizontal: 8, paddingVertical: 3,
    },
    matchPct:  { fontSize: 12, fontWeight: '800', color: '#4A6CF7' },
    openBadge: {
        backgroundColor: '#dcfce7', borderRadius: 8,
        paddingHorizontal: 8, paddingVertical: 3,
    },
    openText:  { fontSize: 10, fontWeight: '700', color: '#16a34a' },

    // Join nudge (no orgs yet)
    joinNudge: {
        flexDirection: 'row', alignItems: 'center', gap: 10,
        backgroundColor: '#fff', borderRadius: 14,
        padding: 14,
        shadowColor: '#4A6CF7', shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.06, shadowRadius: 6, elevation: 2,
    },
    joinNudgeText: { flex: 1, fontSize: 13, fontWeight: '600', color: '#4A6CF7' },
});

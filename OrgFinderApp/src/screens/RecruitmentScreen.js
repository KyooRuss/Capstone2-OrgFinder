import React, { useState, useCallback, useContext } from 'react';
import {
    View, Text, FlatList, TouchableOpacity, StyleSheet,
    Image, ActivityIndicator, RefreshControl, TextInput,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { AuthContext } from '../context/AuthContext';
import api from '../api/client';

const STATUS_COLORS = {
    pending:  { bg: '#fef9c3', text: '#854d0e', label: 'Pending'  },
    accepted: { bg: '#dcfce7', text: '#166534', label: 'Accepted' },
    declined: { bg: '#fee2e2', text: '#991b1b', label: 'Declined' },
};

const MEMBER_STYLE = { bg: '#eff3ff', text: '#4A6CF7', label: 'Member' };

export default function RecruitmentScreen({ navigation }) {
    const { user } = useContext(AuthContext);
    const isProf   = user?.role === 'prof';

    const [orgs, setOrgs]               = useState([]);
    const [userHasOrg, setUserHasOrg]   = useState(false);
    const [loading, setLoading]         = useState(true);
    const [refreshing, setRefreshing]   = useState(false);
    const [search, setSearch]           = useState('');

    const load = useCallback(async () => {
        try {
            const recruitRes = await api.get('/organizations/recruiting');
            setOrgs(recruitRes.data.recruiting ?? []);
            setUserHasOrg(recruitRes.data.user_has_org ?? false);
        } catch {}
        finally { setLoading(false); setRefreshing(false); }
    }, []);

    useFocusEffect(useCallback(() => { load(); }, [load]));

    const filtered = search.trim()
        ? orgs.filter(o => o.name.toLowerCase().includes(search.toLowerCase()))
        : orgs;

    const renderOrg = ({ item }) => {
        const isMember    = item.is_member;
        const statusStyle = isMember ? MEMBER_STYLE : (item.my_status ? STATUS_COLORS[item.my_status] : null);
        // Can only apply if: not a prof, not already in this org, no pending request, and not a member of any org
        const showApply   = !isProf && !isMember && !item.my_status && !userHasOrg;

        return (
            <TouchableOpacity
                style={styles.card}
                onPress={() => navigation.navigate('OrgDetail', { id: item.id })}
                activeOpacity={0.85}
            >
                {/* Logo */}
                {item.logo
                    ? <Image source={{ uri: item.logo }} style={styles.logo} />
                    : <View style={[styles.logo, styles.logoFallback]}>
                        <Text style={styles.logoText}>{item.name?.[0] ?? 'O'}</Text>
                      </View>
                }

                {/* Info */}
                <View style={styles.info}>
                    <Text style={styles.orgName} numberOfLines={1}>{item.name}</Text>
                    {item.category ? (
                        <Text style={styles.category} numberOfLines={1}>
                            {Array.isArray(item.category) ? item.category.join(', ') : item.category}
                        </Text>
                    ) : null}
                    {!isProf && statusStyle && (
                        <View style={[styles.statusBadge, { backgroundColor: statusStyle.bg }]}>
                            <Text style={[styles.statusText, { color: statusStyle.text }]}>
                                {statusStyle.label}
                            </Text>
                        </View>
                    )}
                </View>

                {/* Right */}
                <View style={styles.right}>
                    {showApply && (
                        <View style={styles.applyTag}>
                            <Text style={styles.applyTagText}>Apply</Text>
                        </View>
                    )}
                    <Ionicons name="chevron-forward" size={18} color="#c7d2fe" />
                </View>
            </TouchableOpacity>
        );
    };

    return (
        <View style={styles.root}>
            {/* Header */}
            <LinearGradient colors={['#7CB9FF', '#4A6CF7']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.header}>
                <SafeAreaView>
                    <Text style={styles.headerTitle}>Recruitment</Text>
                    <Text style={styles.headerSub}>Organizations open for membership</Text>
                    <View style={styles.searchWrap}>
                        <Ionicons name="search-outline" size={16} color="#94a3b8" />
                        <TextInput
                            style={styles.searchInput}
                            placeholder="Search organizations..."
                            placeholderTextColor="#94a3b8"
                            value={search}
                            onChangeText={setSearch}
                        />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {loading ? (
                <View style={styles.center}>
                    <ActivityIndicator color="#4A6CF7" size="large" />
                </View>
            ) : (
                <FlatList
                    data={filtered}
                    keyExtractor={item => String(item.id)}
                    renderItem={renderOrg}
                    contentContainerStyle={[
                        styles.list,
                        filtered.length === 0 && styles.emptyWrap,
                    ]}
                    showsVerticalScrollIndicator={false}
                    refreshControl={
                        <RefreshControl
                            refreshing={refreshing}
                            onRefresh={() => { setRefreshing(true); load(); }}
                            colors={['#4A6CF7']}
                        />
                    }
                    ItemSeparatorComponent={() => <View style={styles.sep} />}
                    ListEmptyComponent={
                        <View style={styles.empty}>
                            <Ionicons name="megaphone-outline" size={56} color="#c7d2fe" />
                            <Text style={styles.emptyTitle}>No open recruitments</Text>
                            <Text style={styles.emptyHint}>
                                Check back later for organizations opening membership.
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
    headerTitle: { fontSize: 20, fontWeight: '700', color: '#fff', paddingTop: 8, marginBottom: 2 },
    headerSub:   { fontSize: 13, color: 'rgba(255,255,255,0.8)', marginBottom: 12 },

    searchWrap: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#fff',
        borderRadius: 12,
        paddingHorizontal: 14,
        height: 46,
        gap: 8,
    },
    searchInput: { flex: 1, fontSize: 14, color: '#333' },

    list: { padding: 16, paddingBottom: 90, gap: 0 },
    sep:  { height: 1, backgroundColor: '#e8edf8', marginHorizontal: 0 },

    card: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#fff',
        paddingHorizontal: 16,
        paddingVertical: 14,
        gap: 14,
    },
    logo:         { width: 54, height: 54, borderRadius: 27 },
    logoFallback: { backgroundColor: '#4A6CF7', alignItems: 'center', justifyContent: 'center' },
    logoText:     { color: '#fff', fontSize: 20, fontWeight: '700' },

    info:    { flex: 1, gap: 4 },
    orgName: { fontSize: 15, fontWeight: '700', color: '#1e2f6e' },
    category:{ fontSize: 12, color: '#64748b' },

    statusBadge: {
        alignSelf: 'flex-start',
        borderRadius: 6,
        paddingHorizontal: 8,
        paddingVertical: 3,
    },
    statusText: { fontSize: 11, fontWeight: '700' },

    right:    { alignItems: 'flex-end', gap: 6 },
    applyTag: {
        backgroundColor: '#eff3ff',
        borderRadius: 8,
        paddingHorizontal: 10,
        paddingVertical: 4,
    },
    applyTagText: { fontSize: 12, fontWeight: '700', color: '#4A6CF7' },

    emptyWrap: { flex: 1 },
    empty:     { flex: 1, alignItems: 'center', justifyContent: 'center', paddingTop: 80, paddingHorizontal: 40, gap: 10 },
    emptyTitle:{ fontSize: 16, fontWeight: '700', color: '#64748b' },
    emptyHint: { fontSize: 13, color: '#94a3b8', textAlign: 'center' },

});

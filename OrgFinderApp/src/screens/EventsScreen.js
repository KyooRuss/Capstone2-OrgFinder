import React, { useState, useEffect, useCallback, useContext } from 'react';
import {
    View, Text, TextInput, TouchableOpacity, StyleSheet,
    FlatList, Image, ActivityIndicator, RefreshControl, Modal, ScrollView,
    useWindowDimensions,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { AuthContext } from '../context/AuthContext';
import api from '../api/client';

function buildRows(events) {
    const myOrg = events.filter(e => e.is_my_org);
    const other = events.filter(e => !e.is_my_org);
    const rows = [];
    if (myOrg.length > 0) {
        rows.push({ type: 'header', key: 'h1', label: 'Your Organization' });
        myOrg.forEach(e => rows.push({ type: 'event', key: `e${e.id}`, ...e }));
    }
    if (other.length > 0) {
        rows.push({ type: 'header', key: 'h2', label: 'Other Organizations' });
        other.forEach(e => rows.push({ type: 'event', key: `e${e.id}`, ...e }));
    }
    return rows;
}

export default function EventsScreen({ navigation }) {
    const { user } = useContext(AuthContext);
    const isProf = user?.role === 'prof';
    const { height } = useWindowDimensions();
    const headerSpacing = Math.round(height * 0.03);

    const [rows, setRows]               = useState([]);
    const [orgs, setOrgs]               = useState([]);
    const [loading, setLoading]         = useState(true);
    const [refreshing, setRefreshing]   = useState(false);
    const [search, setSearch]           = useState('');
    const [selectedOrg, setSelectedOrg] = useState(null);
    const [showOrgModal, setShowOrgModal] = useState(false);
    const [department, setDepartment]   = useState('All'); // prof only: filter by department
    const [showDeptModal, setShowDeptModal] = useState(false);

    // Fetch org list once for the filter dropdown (students)
    useEffect(() => {
        if (!isProf) {
            api.get('/organizations').then(res => {
                setOrgs(res.data.organizations.map(o => ({ id: o.id, name: o.name })));
            }).catch(() => {});
        }
    }, [isProf]);

    const loadEvents = useCallback(async () => {
        try {
            const params = {};
            if (search.trim()) params.search = search.trim();
            if (!isProf && selectedOrg) params.org_id = selectedOrg.id;
            if (isProf && department !== 'All') params.department = department;
            const res = await api.get('/events/upcoming', { params });
            setRows(buildRows(res.data.events ?? []));
        } catch {}
        finally { setLoading(false); setRefreshing(false); }
    }, [search, selectedOrg, department, isProf]);

    useEffect(() => { setLoading(true); loadEvents(); }, [selectedOrg, department]);

    const renderRow = ({ item }) => {
        if (item.type === 'header') {
            return <Text style={styles.sectionLabel}>{item.label}</Text>;
        }

        return (
            <TouchableOpacity
                style={styles.eventCard}
                onPress={() => navigation.navigate('EventDetail', { id: item.id })}
                activeOpacity={0.88}
            >
                {item.poster
                    ? <Image source={{ uri: item.poster }} style={styles.poster} />
                    : <View style={styles.posterPlaceholder} />
                }
                <View style={styles.eventInfo}>
                    <Text style={styles.orgTag} numberOfLines={1}>{item.organization?.name}</Text>
                    <Text style={styles.eventTitle} numberOfLines={3}>{item.title}</Text>
                    <View style={styles.metaRow}>
                        <Ionicons name="calendar-outline" size={13} color="#000000" />
                        <Text style={styles.metaText}>{item.date}</Text>
                    </View>
                    {item.time ? (
                        <View style={styles.metaRow}>
                            <Ionicons name="time-outline" size={13} color="#000000" />
                            <Text style={styles.metaText}>{item.time}</Text>
                        </View>
                    ) : null}
                    {item.venue ? (
                        <View style={styles.metaRow}>
                            <Ionicons name="location-outline" size={13} color="#000000" />
                            <Text style={styles.metaText}>{item.venue}</Text>
                        </View>
                    ) : null}
                    <TouchableOpacity
                        style={styles.viewDetailsWrap}
                        onPress={() => navigation.navigate('EventDetail', { id: item.id })}
                    >
                        <Text style={styles.viewDetailsLink}>View Details</Text>
                    </TouchableOpacity>
                </View>
            </TouchableOpacity>
        );
    };

    return (
        <View style={styles.root}>
            <LinearGradient colors={['#7CB9FF', '#4A6CF7']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.header}>
                <SafeAreaView>
                    <View style={[styles.headerRow, { marginBottom: headerSpacing }]}>
                        <Text style={styles.headerTitle}>Upcoming Events</Text>
                    </View>
                    <View style={styles.searchRow}>
                        <View style={styles.searchWrap}>
                            <Ionicons name="search-outline" size={18} color="#aaa" style={styles.searchIcon} />
                            <TextInput
                                style={styles.searchInput}
                                placeholder="Search Event..."
                                placeholderTextColor="#aaa"
                                value={search}
                                onChangeText={setSearch}
                                onSubmitEditing={() => { setLoading(true); loadEvents(); }}
                                returnKeyType="search"
                            />
                        </View>
                        {isProf ? (
                            <TouchableOpacity
                                style={[styles.filterBtn, department !== 'All' && styles.filterBtnActive]}
                                onPress={() => setShowDeptModal(true)}
                            >
                                <Text style={[styles.filterBtnText, department !== 'All' && { color: '#fff' }]} numberOfLines={1}>
                                    {department === 'All' ? 'Dept ▼' : department.replace('College of ', '') + ' ▼'}
                                </Text>
                            </TouchableOpacity>
                        ) : (
                            <TouchableOpacity style={styles.filterBtn} onPress={() => setShowOrgModal(true)}>
                                <Text style={styles.filterBtnText} numberOfLines={1}>
                                    {selectedOrg ? selectedOrg.name : 'Filter'} ▼
                                </Text>
                            </TouchableOpacity>
                        )}
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {/* Department filter modal (prof only) */}
            <Modal visible={showDeptModal} transparent animationType="fade" onRequestClose={() => setShowDeptModal(false)}>
                <TouchableOpacity style={[styles.modalOverlay, { paddingTop: Math.round(height * 0.15) }]} activeOpacity={1} onPress={() => setShowDeptModal(false)}>
                    <View style={styles.modalBox}>
                        <ScrollView nestedScrollEnabled showsVerticalScrollIndicator={false}>
                            {['All', 'College of Information Technology', 'College of Engineering', 'College of Nursing', 'College of Business Administration', 'College of Arts and Sciences', 'College of Education', 'College of Criminology'].map(dept => (
                                <TouchableOpacity
                                    key={dept}
                                    style={[styles.modalItem, dept === department && styles.modalItemActive]}
                                    onPress={() => { setDepartment(dept); setShowDeptModal(false); }}
                                >
                                    <Text style={[styles.modalItemText, dept === department && styles.modalItemTextActive]}>
                                        {dept}
                                    </Text>
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>
                </TouchableOpacity>
            </Modal>

            {/* Org filter modal */}
            <Modal visible={showOrgModal} transparent animationType="fade" onRequestClose={() => setShowOrgModal(false)}>
                <TouchableOpacity style={[styles.modalOverlay, { paddingTop: Math.round(height * 0.15) }]} activeOpacity={1} onPress={() => setShowOrgModal(false)}>
                    <View style={styles.modalBox}>
                        <ScrollView nestedScrollEnabled showsVerticalScrollIndicator={false}>
                            <TouchableOpacity
                                style={[styles.modalItem, !selectedOrg && styles.modalItemActive]}
                                onPress={() => { setSelectedOrg(null); setShowOrgModal(false); }}
                            >
                                <Text style={[styles.modalItemText, !selectedOrg && styles.modalItemTextActive]}>
                                    All Organizations
                                </Text>
                            </TouchableOpacity>
                            {orgs.map(org => (
                                <TouchableOpacity
                                    key={org.id}
                                    style={[styles.modalItem, selectedOrg?.id === org.id && styles.modalItemActive]}
                                    onPress={() => { setSelectedOrg(org); setShowOrgModal(false); }}
                                >
                                    <Text style={[styles.modalItemText, selectedOrg?.id === org.id && styles.modalItemTextActive]}>
                                        {org.name}
                                    </Text>
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>
                </TouchableOpacity>
            </Modal>

            {loading ? (
                <ActivityIndicator style={{ marginTop: 60 }} color="#4A6CF7" size="large" />
            ) : (
                <FlatList
                    data={rows}
                    keyExtractor={item => item.key}
                    renderItem={renderRow}
                    contentContainerStyle={styles.list}
                    showsVerticalScrollIndicator={false}
                    refreshControl={
                        <RefreshControl
                            refreshing={refreshing}
                            onRefresh={() => { setRefreshing(true); loadEvents(); }}
                            colors={['#4A6CF7']}
                        />
                    }
                    ListEmptyComponent={<Text style={styles.empty}>No upcoming events found.</Text>}
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#e8eff7' },
    header: { paddingHorizontal: 16, paddingBottom: 20 },
    headerRow: { flexDirection: 'row', alignItems: 'center', paddingTop: 8 },
    backBtn: { padding: 4, marginRight: 8 },
    backIcon: { color: '#fff', fontSize: 28, lineHeight: 28 },
    headerTitle: { color: '#fff', fontSize: 20, fontWeight: '700' },
    searchRow: { flexDirection: 'row', alignItems: 'center', gap: 5 },
    searchWrap: {
        flex: 1, flexDirection: 'row', alignItems: 'center',
        backgroundColor: '#fff', borderRadius: 12, paddingHorizontal: 14, height: 46,
    },
    searchIcon: { marginRight: 6 },
    searchInput: { flex: 1, fontSize: 14, color: '#333' },
    filterBtn: {
        backgroundColor: '#fff', borderRadius: 12, height: 46,
        paddingHorizontal: 12, alignItems: 'center', justifyContent: 'center',
        maxWidth: 110,
    },
    filterBtnActive: { backgroundColor: '#4A6CF7' },
    filterBtnText: { fontSize: 12, fontWeight: '600', color: '#4A6CF7' },

    // Org modal
    modalOverlay: {
        flex: 1, backgroundColor: 'rgba(0,0,0,0.35)',
        justifyContent: 'flex-start', paddingHorizontal: 16,
        alignItems: 'flex-end',
    },
    modalBox: {
        backgroundColor: '#fff', borderRadius: 14, overflow: 'hidden',
        elevation: 10, minWidth: 220, maxHeight: 300,
        shadowColor: '#000', shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.15, shadowRadius: 12,
    },
    modalItem: { paddingVertical: 13, paddingHorizontal: 18, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    modalItemActive: { backgroundColor: '#eff6ff' },
    modalItemText: { fontSize: 14, color: '#334155' },
    modalItemTextActive: { color: '#4A6CF7', fontWeight: '700' },

    sectionLabel: {
        fontSize: 11,
        fontWeight: '700',
        color: '#94a3b8',
        letterSpacing: 0.8,
        textTransform: 'uppercase',
        paddingHorizontal: 4,
        paddingTop: 16,
        paddingBottom: 8,
    },

    // Cards
    list: { padding: 16, paddingBottom: 90, gap: 14 },
    eventCard: {
        backgroundColor: '#fff', borderRadius: 16,
        flexDirection: 'row', alignItems: 'stretch',
        overflow: 'hidden',
        elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.08, shadowRadius: 6,
    },
    poster: { width: 110, height: 130 },
    posterPlaceholder: { width: 110, height: 130, backgroundColor: '#dde4f0' },
    eventInfo: { flex: 1, padding: 12, justifyContent: 'space-between' },
    orgTag: { fontSize: 11, fontWeight: '600', color: '#4A6CF7', marginBottom: 4 },
    eventTitle: { fontSize: 14, fontWeight: '700', color: '#0f2044', marginBottom: 8, lineHeight: 20 },
    metaRow: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 3 },
    metaText: { fontSize: 12, color: '#334155', flex: 1 },
    viewDetailsWrap: { alignSelf: 'flex-end', marginTop: 8 },
    viewDetailsLink: {
        fontSize: 12, fontWeight: '700', color: '#1e3a6e',
        textDecorationLine: 'underline',
    },
    empty: { textAlign: 'center', marginTop: 60, color: '#888', fontSize: 15 },
});

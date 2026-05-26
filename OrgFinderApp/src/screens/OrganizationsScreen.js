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

const CATEGORIES = [
    'All', 'Technology', 'Programming', 'Networking', 'Arts', 'Leadership',
    'Research', 'Dancing', 'Photography', 'Sign Language', 'Gaming',
    'Music Publishing', 'Singing', 'Innovation', 'Photo Video Editing',
    'Mental First Aid', 'Acting', 'Recording Production',
];

const DEPARTMENTS = [
    'All',
    'College of Information Technology',
    'College of Engineering',
    'College of Nursing',
    'College of Business Administration',
    'College of Arts and Sciences',
    'College of Education',
    'College of Criminology',
];

export default function OrganizationsScreen({ navigation }) {
    const { user } = useContext(AuthContext);
    const isProf = user?.role === 'prof';
    const { width, height } = useWindowDimensions();
    const headerSpacing = Math.round(height * 0.03);

    const [orgs, setOrgs]             = useState([]);
    const [loading, setLoading]       = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [search, setSearch]         = useState('');
    const [category, setCategory]     = useState('All');
    const [department, setDepartment] = useState('All');
    const [showFilterModal, setShowFilterModal] = useState(false);

    const loadOrgs = useCallback(async () => {
        try {
            const params = {};
            if (search.trim()) params.search = search.trim();
            if (!isProf && category !== 'All') params.category = category;
            if (isProf && department !== 'All') params.department = department;
            const res = await api.get('/organizations', { params });
            setOrgs(res.data.organizations);
        } catch {}
        finally { setLoading(false); setRefreshing(false); }
    }, [search, category, department, isProf]);

    useEffect(() => { setLoading(true); loadOrgs(); }, [category, department]);

    const renderOrg = ({ item }) => {
        const logoSize = Math.min(Math.round(width * 0.15), 64);
        return (
            <TouchableOpacity
                style={styles.orgCard}
                onPress={() => navigation.navigate('OrgDetail', { id: item.id })}
                activeOpacity={0.85}
            >
                {item.logo
                    ? <Image source={{ uri: item.logo }} style={[styles.orgLogo, { width: logoSize, height: logoSize, borderRadius: logoSize / 2 }]} />
                    : <View style={[styles.orgLogo, styles.orgLogoFallback, { width: logoSize, height: logoSize, borderRadius: logoSize / 2 }]}>
                        <Text style={styles.orgLogoText}>{item.name?.[0] ?? 'O'}</Text>
                      </View>
                }
                <View style={styles.cardBody}>
                    <Text style={styles.orgName} numberOfLines={2}>{item.name}</Text>
                    {item.mission ? (
                        <Text style={styles.orgMission} numberOfLines={2}>{item.mission}</Text>
                    ) : null}
                    <Text style={styles.viewDetails}>View Details</Text>
                </View>
            </TouchableOpacity>
        );
    };

    return (
        <View style={styles.root}>
            <LinearGradient colors={['#7CB9FF', '#4A6CF7']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.header}>
                <SafeAreaView>
                    <View style={[styles.headerRow, { marginBottom: headerSpacing }]}>
                        <Text style={styles.headerTitle}>Organizations</Text>
                    </View>

                    <View style={styles.searchRow}>
                        <View style={styles.searchWrap}>
                            <Ionicons name="search-outline" size={18} color="#aaa" style={styles.searchIcon} />
                            <TextInput
                                style={styles.searchInput}
                                placeholder="Search Organization..."
                                placeholderTextColor="#aaa"
                                value={search}
                                onChangeText={setSearch}
                                onSubmitEditing={() => { setLoading(true); loadOrgs(); }}
                                returnKeyType="search"
                            />
                        </View>
                        {isProf ? (
                            <TouchableOpacity
                                style={[styles.catBtn, department !== 'All' && styles.catBtnActive]}
                                onPress={() => setShowFilterModal(true)}
                            >
                                <Text style={[styles.catBtnText, department !== 'All' && { color: '#fff' }]} numberOfLines={1}>
                                    {department === 'All' ? 'Dept ▼' : department.replace('College of ', '') + ' ▼'}
                                </Text>
                            </TouchableOpacity>
                        ) : (
                            <TouchableOpacity style={styles.catBtn} onPress={() => setShowFilterModal(true)}>
                                <Text style={styles.catBtnText} numberOfLines={1}>
                                    {category === 'All' ? 'Category ▼' : category + ' ▼'}
                                </Text>
                            </TouchableOpacity>
                        )}
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {/* Filter dropdown modal */}
            <Modal visible={showFilterModal} transparent animationType="fade" onRequestClose={() => setShowFilterModal(false)}>
                <TouchableOpacity style={[styles.modalOverlay, { paddingTop: Math.round(height * 0.15) }]} activeOpacity={1} onPress={() => setShowFilterModal(false)}>
                    <View style={styles.modalBox}>
                        <ScrollView nestedScrollEnabled showsVerticalScrollIndicator={false}>
                            {(isProf ? DEPARTMENTS : CATEGORIES).map(item => {
                                const active = isProf ? item === department : item === category;
                                return (
                                    <TouchableOpacity
                                        key={item}
                                        style={[styles.modalItem, active && styles.modalItemActive]}
                                        onPress={() => {
                                            if (isProf) setDepartment(item);
                                            else setCategory(item);
                                            setShowFilterModal(false);
                                        }}
                                    >
                                        <Text style={[styles.modalItemText, active && styles.modalItemTextActive]}>
                                            {item}
                                        </Text>
                                    </TouchableOpacity>
                                );
                            })}
                        </ScrollView>
                    </View>
                </TouchableOpacity>
            </Modal>

            {loading ? (
                <ActivityIndicator style={{ marginTop: 60 }} color="#4A6CF7" size="large" />
            ) : (
                <FlatList
                    data={orgs}
                    keyExtractor={item => String(item.id)}
                    renderItem={renderOrg}
                    contentContainerStyle={styles.list}
                    showsVerticalScrollIndicator={false}
                    refreshControl={
                        <RefreshControl
                            refreshing={refreshing}
                            onRefresh={() => { setRefreshing(true); loadOrgs(); }}
                            colors={['#4A6CF7']}
                        />
                    }
                    ListEmptyComponent={<Text style={styles.empty}>No organizations found.</Text>}
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#f5f6fa' },
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
    catBtn: {
        backgroundColor: '#fff', borderRadius: 12, height: 46,
        paddingHorizontal: 14, alignItems: 'center', justifyContent: 'center',
    },
    catBtnActive: { backgroundColor: '#4A6CF7' },
    catBtnText: { fontSize: 13, fontWeight: '600', color: '#4A6CF7' },

    // Category modal
    modalOverlay: {
        flex: 1, backgroundColor: 'rgba(0,0,0,0.35)',
        justifyContent: 'flex-start', paddingHorizontal: 16,
        alignItems: 'flex-end',
    },
    modalBox: {
        backgroundColor: '#fff', borderRadius: 14, overflow: 'hidden',
        elevation: 10, minWidth: 200, maxHeight: 280,
        shadowColor: '#000', shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.15, shadowRadius: 12,
    },
    modalItem: { paddingVertical: 13, paddingHorizontal: 18, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    modalItemActive: { backgroundColor: '#eff6ff' },
    modalItemText: { fontSize: 14, color: '#334155' },
    modalItemTextActive: { color: '#4A6CF7', fontWeight: '700' },

    // List
    list: { paddingHorizontal: 16, paddingTop: 16, paddingBottom: 90, gap: 12 },
    orgCard: {
        backgroundColor: '#fff', borderRadius: 14,
        flexDirection: 'row', alignItems: 'center',
        padding: 14, gap: 14,
        shadowColor: '#000', shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.07, shadowRadius: 8, elevation: 3,
    },
    orgLogo: { flexShrink: 0 },
    orgLogoFallback: { backgroundColor: '#4A6CF7', alignItems: 'center', justifyContent: 'center' },
    orgLogoText: { color: '#fff', fontSize: 22, fontWeight: '700' },
    cardBody: { flex: 1 },
    orgName: { fontSize: 15, fontWeight: '700', color: '#1e2f6e', marginBottom: 4 },
    orgMission: { fontSize: 12, color: '#666', lineHeight: 17, marginBottom: 6 },
    viewDetails: { fontSize: 12, color: '#4A6CF7', fontWeight: '600', textAlign: 'right'},
    empty: { textAlign: 'center', marginTop: 60, color: '#888', fontSize: 15 },
});

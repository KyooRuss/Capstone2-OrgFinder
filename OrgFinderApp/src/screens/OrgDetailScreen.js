import React, { useState, useEffect, useContext } from 'react';
import {
    View, Text, ScrollView, Image, StyleSheet,
    TouchableOpacity, ActivityIndicator, useWindowDimensions,
    Modal, StatusBar, TextInput, Alert, KeyboardAvoidingView, Platform,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { AuthContext } from '../context/AuthContext';
import api from '../api/client';

function JoinInfoRow({ label, value }) {
    return (
        <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 }}>
            <Text style={{ fontSize: 12, color: '#94a3b8', width: 90 }}>{label}</Text>
            <Text style={{ fontSize: 12, color: '#1e2f6e', fontWeight: '600', flex: 1, textAlign: 'right' }}>{value}</Text>
        </View>
    );
}

export default function OrgDetailScreen({ route, navigation }) {
    const { id } = route.params;
    const { user } = useContext(AuthContext);
    const { width, height } = useWindowDimensions();
    const [org, setOrg]               = useState(null);
    const [loading, setLoading]       = useState(true);
    const [photoIndex, setPhotoIndex] = useState(null);
    const [showJoinModal, setShowJoinModal] = useState(false);
    const [socialLink, setSocialLink]       = useState('');
    const [applying, setApplying]           = useState(false);

    const logoSize = Math.min(Math.round(width * 0.19), 80);
    const photoW   = Math.min(Math.round(width * 0.46), 200);
    const photoH   = Math.round(photoW * 0.65);

    useEffect(() => {
        api.get(`/organizations/${id}`)
            .then(res => setOrg(res.data.organization))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, [id]);

    const handleApply = async () => {
        if (!socialLink.trim()) {
            Alert.alert('Required', 'Please enter your social media link.');
            return;
        }
        setApplying(true);
        try {
            await api.post(`/organizations/${id}/apply`, { social_media_link: socialLink.trim() });
            setOrg(prev => ({ ...prev, my_request_status: 'pending' }));
            setShowJoinModal(false);
            setSocialLink('');
            Alert.alert('Application Sent', 'Your application has been submitted. The organization officer will contact you for an interview.');
        } catch (err) {
            Alert.alert('Error', err.response?.data?.message ?? 'Something went wrong.');
        } finally {
            setApplying(false);
        }
    };

    if (loading) return (
        <View style={styles.center}>
            <ActivityIndicator color="#4A6CF7" size="large" />
        </View>
    );

    if (!org) return (
        <View style={styles.center}><Text>Organization not found.</Text></View>
    );

    return (
        <View style={styles.root}>
            <ScrollView showsVerticalScrollIndicator={false}>
                <LinearGradient colors={['#7CB9FF', '#4A6CF7']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.header}>
                    <View style={styles.hero}>
                        <SafeAreaView>
                            <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                                <Text style={styles.backIcon}>‹</Text>
                            </TouchableOpacity>
                        </SafeAreaView>

                        <View style={styles.heroContent}>
                            {org.logo
                                ? <Image source={{ uri: org.logo }} style={[styles.heroLogo, { width: logoSize, height: logoSize, borderRadius: logoSize / 2 }]} />
                                : <View style={[styles.heroLogo, styles.heroLogoFallback, { width: logoSize, height: logoSize, borderRadius: logoSize / 2 }]}>
                                    <Ionicons name="business" size={Math.round(logoSize * 0.45)} color="#fff" />
                                  </View>
                            }
                            <View style={styles.heroTextBlock}>
                                <Text style={styles.heroName} numberOfLines={2}>{org.name}</Text>
                                {org.president ? (
                                    <Text style={styles.heroPresident}>President: {org.president}</Text>
                                ) : null}

                                {/* Recruiting badge + Join button */}
                                {org.is_recruiting && (
                                    <View style={styles.recruitRow}>
                                        <View style={styles.recruitBadge}>
                                            <Text style={styles.recruitBadgeText}>Now Recruiting</Text>
                                        </View>
                                        {org.my_request_status === 'pending' ? (
                                            <View style={styles.joinSentBtn}>
                                                <Text style={styles.joinSentText}>Application Sent</Text>
                                            </View>
                                        ) : org.my_request_status === 'accepted' ? (
                                            <View style={[styles.joinSentBtn, { backgroundColor: 'rgba(22,163,74,0.2)' }]}>
                                                <Text style={[styles.joinSentText, { color: '#bbf7d0' }]}>Accepted</Text>
                                            </View>
                                        ) : org.my_request_status === 'declined' ? (
                                            <View style={[styles.joinSentBtn, { backgroundColor: 'rgba(220,38,38,0.2)' }]}>
                                                <Text style={[styles.joinSentText, { color: '#fca5a5' }]}>Declined</Text>
                                            </View>
                                        ) : (
                                            <TouchableOpacity style={styles.joinBtn} onPress={() => setShowJoinModal(true)}>
                                                <Text style={styles.joinBtnText}>Join</Text>
                                            </TouchableOpacity>
                                        )}
                                    </View>
                                )}
                            </View>
                        </View>
                    </View>
                </LinearGradient>

                <View style={styles.body}>
                    {/* Group Chat button — visible only to accepted members */}
                    {org.is_member && (
                        <TouchableOpacity
                            style={styles.chatBtn}
                            onPress={() => navigation.navigate('OrgChat', { orgId: org.id, orgName: org.name })}
                            activeOpacity={0.85}
                        >
                            <Ionicons name="chatbubbles" size={18} color="#fff" style={{ marginRight: 8 }} />
                            <Text style={styles.chatBtnText}>Group Chat</Text>
                        </TouchableOpacity>
                    )}

                    {/* About */}
                    {org.mission ? (
                        <View style={styles.section}>
                            <Text style={styles.sectionTitle}>About</Text>
                            <Text style={styles.descText}>{org.mission}</Text>
                        </View>
                    ) : null}

                    {/* Why Join */}
                    {org.reasons?.length > 0 && (
                        <View style={styles.section}>
                            <Text style={styles.sectionTitle}>Why Join {org.name}?</Text>
                            {org.reasons.map((r, i) => (
                                <View key={i} style={styles.reasonRow}>
                                    <Text style={styles.reasonCheck}>✔</Text>
                                    <Text style={styles.reasonText}>{r}</Text>
                                </View>
                            ))}
                        </View>
                    )}

                    {/* Events & Activities photos */}
                    {org.photos?.length > 0 && (
                        <View style={styles.section}>
                            <Text style={styles.sectionTitle}>Events & Activities</Text>
                            <ScrollView
                                horizontal
                                showsHorizontalScrollIndicator={false}
                                nestedScrollEnabled
                                contentContainerStyle={styles.photosRow}
                            >
                                {org.photos.map((p, i) => (
                                    <TouchableOpacity key={i} activeOpacity={0.85} onPress={() => setPhotoIndex(i)}>
                                        <Image source={{ uri: p }} style={{ width: photoW, height: photoH, borderRadius: 10 }} />
                                    </TouchableOpacity>
                                ))}
                            </ScrollView>
                        </View>
                    )}

                    {/* Testimonials */}
                    {org.testimonials?.length > 0 && (
                        <View style={styles.section}>
                            <View style={styles.sectionHeaderBtn}>
                                <Text style={styles.sectionTitleWhite}>Members' Experience</Text>
                            </View>
                            {org.testimonials.map((t, i) => (
                                <View key={i} style={styles.testimonialCard}>
                                    <Text style={styles.testimonialText}>"{t.text}"</Text>
                                    {t.author ? (
                                        <Text style={styles.testimonialAuthor}>— {t.author}</Text>
                                    ) : null}
                                </View>
                            ))}
                        </View>
                    )}

                    {/* Core info */}
                    <View style={styles.coreSection}>
                        <View style={styles.coreBadge}>
                            <Text style={styles.coreBadgeText}>Core Information</Text>
                        </View>
                        {org.vision ? (
                            <>
                                <Text style={styles.coreLabel}>Vision</Text>
                                <Text style={styles.coreText}>{org.vision}</Text>
                            </>
                        ) : null}
                        {org.mission ? (
                            <>
                                <Text style={styles.coreLabel}>Mission</Text>
                                <Text style={styles.coreText}>{org.mission}</Text>
                            </>
                        ) : null}
                    </View>

                    {/* Footer contact info */}
                    {(org.room_number || org.contact_telegram || org.contact_facebook) && (
                        <View style={styles.footer}>
                            <View style={styles.footerGrid}>
                                {org.room_number && (
                                    <View style={styles.footerItem}>
                                        <Ionicons name="location-outline" size={15} color="rgba(255,255,255,0.75)" />
                                        <Text style={styles.footerText}>{org.room_number}</Text>
                                    </View>
                                )}
                                {org.contact_facebook && (
                                    <View style={styles.footerItem}>
                                        <Ionicons name="person-outline" size={15} color="rgba(255,255,255,0.75)" />
                                        <Text style={styles.footerText}>{org.contact_facebook}</Text>
                                    </View>
                                )}
                                {org.contact_telegram && (
                                    <View style={styles.footerItem}>
                                        <Ionicons name="call-outline" size={15} color="rgba(255,255,255,0.75)" />
                                        <Text style={styles.footerText}>{org.contact_telegram}</Text>
                                    </View>
                                )}
                            </View>
                        </View>
                    )}
                </View>
            </ScrollView>

            {/* Join / Application modal */}
            <Modal visible={showJoinModal} transparent animationType="slide" onRequestClose={() => setShowJoinModal(false)}>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1 }}>
                    <View style={styles.joinModalOverlay}>
                        <View style={styles.joinModalSheet}>
                            <View style={styles.joinModalHandle} />
                            <Text style={styles.joinModalTitle}>Join {org?.name}</Text>

                            {/* Auto-filled info */}
                            <View style={styles.joinInfoBox}>
                                <JoinInfoRow label="Name" value={`${user?.first_name ?? ''} ${user?.last_name ?? ''}`} />
                                <JoinInfoRow label="Student No." value={user?.student_number ?? '—'} />
                                <JoinInfoRow label="Program" value={user?.program ?? '—'} />
                                <JoinInfoRow label="Email" value={user?.email ?? '—'} />
                            </View>

                            {/* Social media input */}
                            <Text style={styles.joinLabel}>Social Media Link <Text style={{ color: '#ef4444' }}>*</Text></Text>
                            <TextInput
                                style={styles.joinInput}
                                placeholder="e.g. facebook.com/yourprofile"
                                placeholderTextColor="#bbb"
                                value={socialLink}
                                onChangeText={setSocialLink}
                                autoCapitalize="none"
                            />

                            {/* Reminder note */}
                            <View style={styles.joinReminder}>
                                <Text style={styles.joinReminderText}>
                                    ⚠️  Submitting this form does not guarantee membership. The organization officer will contact you via your social media or email to schedule an interview.
                                </Text>
                            </View>

                            {/* Buttons */}
                            <View style={styles.joinActions}>
                                <TouchableOpacity style={styles.joinCancelBtn} onPress={() => { setShowJoinModal(false); setSocialLink(''); }}>
                                    <Text style={styles.joinCancelText}>Cancel</Text>
                                </TouchableOpacity>
                                <TouchableOpacity style={[styles.joinSubmitBtn, applying && { opacity: 0.7 }]} onPress={handleApply} disabled={applying}>
                                    {applying
                                        ? <ActivityIndicator color="#fff" size="small" />
                                        : <Text style={styles.joinSubmitText}>Submit Application</Text>
                                    }
                                </TouchableOpacity>
                            </View>
                        </View>
                    </View>
                </KeyboardAvoidingView>
            </Modal>

            {/* Full-screen swipeable photo viewer */}
            <Modal visible={photoIndex !== null} transparent animationType="fade" onRequestClose={() => setPhotoIndex(null)}>
                <StatusBar backgroundColor="#000" barStyle="light-content" />
                <View style={styles.photoModal}>
                    <ScrollView
                        horizontal
                        pagingEnabled
                        showsHorizontalScrollIndicator={false}
                        contentOffset={{ x: (photoIndex ?? 0) * width, y: 0 }}
                        scrollEventThrottle={16}
                        onMomentumScrollEnd={e => {
                            const idx = Math.round(e.nativeEvent.contentOffset.x / width);
                            setPhotoIndex(idx);
                        }}
                    >
                        {(org?.photos ?? []).map((p, i) => (
                            <View key={i} style={{ width, height, alignItems: 'center', justifyContent: 'center' }}>
                                <Image source={{ uri: p }} style={{ width, height, resizeMode: 'contain' }} />
                            </View>
                        ))}
                    </ScrollView>

                    {/* Close button */}
                    <TouchableOpacity style={styles.photoModalClose} onPress={() => setPhotoIndex(null)}>
                        <Text style={styles.photoModalCloseText}>✕</Text>
                    </TouchableOpacity>

                    {/* Page indicator */}
                    {org?.photos?.length > 1 && (
                        <View style={styles.photoModalDots}>
                            {org.photos.map((_, i) => (
                                <View key={i} style={[styles.dot, i === photoIndex && styles.dotActive]} />
                            ))}
                        </View>
                    )}
                </View>
            </Modal>
        </View>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#f5f6fa' },
    center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
    header: {},
    hero: { paddingBottom: 24 },
    backBtn: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 18 },
    backIcon: { color: '#fff', fontSize: 28, lineHeight: 28, marginRight: 4 },
    backLabel: { color: '#fff', fontSize: 14, fontWeight: '600' },
    heroContent: {
        flexDirection: 'row', alignItems: 'center',
        paddingHorizontal: 20, gap: 16,
    },
    heroLogo: { borderWidth: 3, borderColor: 'rgba(255,255,255,0.5)' },
    heroLogoFallback: { backgroundColor: 'rgba(255,255,255,0.25)', alignItems: 'center', justifyContent: 'center' },
    heroTextBlock: { flex: 1 },
    heroName: { fontSize: 20, fontWeight: '800', color: '#fff', marginBottom: 4 },
    heroPresident: { fontSize: 13, color: 'rgba(255,255,255,0.75)', fontStyle: 'italic' },
    body: { padding: 16 },
    chatBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#4A6CF7',
        borderRadius: 12,
        paddingVertical: 13,
        marginBottom: 14,
        shadowColor: '#4A6CF7',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.25,
        shadowRadius: 8,
        elevation: 4,
    },
    chatBtnText: { fontSize: 15, fontWeight: '700', color: '#fff' },
    section: {
        backgroundColor: '#fff', borderRadius: 14, padding: 16, marginBottom: 14,
        elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.06, shadowRadius: 6,
    },
    sectionTitle: { fontSize: 16, fontWeight: '700', color: '#1e2f6e', marginBottom: 12 },
    reasonRow: { flexDirection: 'row', alignItems: 'flex-start', marginBottom: 8, gap: 8 },
    reasonCheck: { fontSize: 14 },
    reasonText: { flex: 1, fontSize: 14, color: '#444', lineHeight: 20 },
    photosRow: { gap: 10, paddingHorizontal: 2 },
    sectionHeaderBtn: {
        backgroundColor: '#4A6CF7', borderRadius: 8, padding: 10,
        marginBottom: 12, alignSelf: 'flex-start',
    },
    sectionTitleWhite: { color: '#fff', fontWeight: '700', fontSize: 14 },
    descText: { fontSize: 14, color: '#555', lineHeight: 22 },
    testimonialCard: {
        backgroundColor: '#eef2ff', borderRadius: 10, padding: 14,
        marginBottom: 10, borderLeftWidth: 3, borderLeftColor: '#4A6CF7',
    },
    testimonialText: { fontSize: 13, color: '#333', lineHeight: 20, fontStyle: 'italic' },
    testimonialAuthor: { fontSize: 12, color: '#4A6CF7', fontWeight: '600', marginTop: 8, textAlign: 'right' },
    coreSection: {
        backgroundColor: '#fff', borderRadius: 14, padding: 16, marginBottom: 14,
        elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.06, shadowRadius: 6,
    },
    coreBadge: {
        backgroundColor: '#4A6CF7', alignSelf: 'flex-start', borderRadius: 8,
        paddingHorizontal: 22, paddingVertical: 10, marginBottom: 14,
    },
    coreBadgeText: { color: '#fff', fontWeight: '700', fontSize: 14 },
    coreLabel: { fontSize: 15, fontWeight: '700', color: '#1e2f6e', marginBottom: 6, marginTop: 8 },
    coreText: { fontSize: 14, color: '#555', lineHeight: 21 },
    footer: {
        backgroundColor: '#1e2f6e',
        borderRadius: 14, padding: 16, marginBottom: 20,
    },
    footerGrid: {
        flexDirection: 'row', flexWrap: 'wrap', gap: 14,
    },
    footerItem: {
        flexDirection: 'row', alignItems: 'center', gap: 6,
        width: '45%',
    },
    footerText: { color: 'rgba(255,255,255,0.85)', fontSize: 12, flex: 1 },

    // Recruit badge + join button
    recruitRow: { flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: 8, flexWrap: 'wrap' },
    recruitBadge: {
        backgroundColor: 'rgba(255,255,255,0.25)', borderRadius: 20,
        paddingHorizontal: 10, paddingVertical: 3,
    },
    recruitBadgeText: { fontSize: 11, fontWeight: '700', color: '#fff' },
    joinBtn: {
        backgroundColor: '#fff', borderRadius: 20,
        paddingHorizontal: 14, paddingVertical: 4,
    },
    joinBtnText: { fontSize: 12, fontWeight: '700', color: '#4A6CF7' },
    joinSentBtn: {
        backgroundColor: 'rgba(255,255,255,0.15)', borderRadius: 20,
        paddingHorizontal: 12, paddingVertical: 4,
    },
    joinSentText: { fontSize: 11, fontWeight: '600', color: 'rgba(255,255,255,0.8)' },

    // Join modal
    joinModalOverlay: {
        flex: 1, backgroundColor: 'rgba(0,0,0,0.5)',
        justifyContent: 'flex-end',
    },
    joinModalSheet: {
        backgroundColor: '#fff', borderTopLeftRadius: 24, borderTopRightRadius: 24,
        padding: 24, paddingBottom: 36,
    },
    joinModalHandle: {
        width: 44, height: 4, borderRadius: 2, backgroundColor: '#d1d5db',
        alignSelf: 'center', marginBottom: 16,
    },
    joinModalTitle: { fontSize: 18, fontWeight: '800', color: '#1e2f6e', marginBottom: 14 },
    joinInfoBox: {
        backgroundColor: '#f5f7ff', borderRadius: 12,
        padding: 14, marginBottom: 16,
    },
    joinLabel: { fontSize: 13, fontWeight: '600', color: '#334155', marginBottom: 6 },
    joinInput: {
        backgroundColor: '#f5f5f5', borderRadius: 10, paddingHorizontal: 14,
        paddingVertical: 12, fontSize: 14, color: '#333',
        borderWidth: 1, borderColor: '#e8e8e8', marginBottom: 14,
    },
    joinReminder: {
        backgroundColor: '#fffbeb', borderRadius: 10, padding: 12,
        borderWidth: 1, borderColor: '#fde68a', marginBottom: 20,
    },
    joinReminderText: { fontSize: 12, color: '#92400e', lineHeight: 18 },
    joinActions: { flexDirection: 'row', gap: 12 },
    joinCancelBtn: {
        flex: 1, paddingVertical: 13, borderRadius: 24,
        borderWidth: 1.5, borderColor: '#ddd', alignItems: 'center',
    },
    joinCancelText: { fontSize: 14, color: '#555', fontWeight: '600' },
    joinSubmitBtn: {
        flex: 2, paddingVertical: 13, borderRadius: 24,
        backgroundColor: '#1e3a8a', alignItems: 'center',
    },
    joinSubmitText: { fontSize: 14, color: '#fff', fontWeight: '700' },

    // Photo viewer
    photoModal: { flex: 1, backgroundColor: '#000' },
    photoModalClose: {
        position: 'absolute', top: 50, right: 20,
        backgroundColor: 'rgba(255,255,255,0.15)',
        borderRadius: 20, width: 40, height: 40,
        alignItems: 'center', justifyContent: 'center',
    },
    photoModalCloseText: { color: '#fff', fontSize: 18, fontWeight: '700' },
    photoModalDots: {
        position: 'absolute', bottom: 40,
        flexDirection: 'row', alignSelf: 'center', gap: 8,
    },
    dot: {
        width: 7, height: 7, borderRadius: 4,
        backgroundColor: 'rgba(255,255,255,0.35)',
    },
    dotActive: { backgroundColor: '#fff', width: 18 },
});

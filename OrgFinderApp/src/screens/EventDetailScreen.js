import React, { useState, useEffect } from 'react';
import {
    View, Text, ScrollView, Image, StyleSheet,
    TouchableOpacity, ActivityIndicator,
    useWindowDimensions,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import api from '../api/client';

export default function EventDetailScreen({ route, navigation }) {
    const { id } = route.params;
    const { height } = useWindowDimensions();
    const [event, setEvent]   = useState(null);
    const [loading, setLoading] = useState(true);

    const sheetMaxHeight = Math.round(height * 0.78);

    useEffect(() => {
        api.get(`/events/${id}`)
            .then(res => setEvent(res.data.event))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, [id]);

    if (loading) return (
        <View style={styles.root}>
            <View style={styles.backdrop} />
            <View style={[styles.sheet, { maxHeight: sheetMaxHeight }]}>
                <ActivityIndicator color="#4A6CF7" size="large" style={{ marginTop: 40 }} />
            </View>
        </View>
    );

    if (!event) return (
        <View style={styles.root}>
            <TouchableOpacity style={styles.backdrop} onPress={() => navigation.goBack()} activeOpacity={1} />
            <View style={[styles.sheet, { maxHeight: sheetMaxHeight }]}>
                <View style={styles.dragHandle} />
                <Text style={{ textAlign: 'center', color: '#888', marginTop: 40 }}>Event not found.</Text>
            </View>
        </View>
    );

    return (
        <View style={styles.root}>
            {/* Dark backdrop — tap to go back */}
            <TouchableOpacity style={styles.backdrop} onPress={() => navigation.goBack()} activeOpacity={1}>
                <SafeAreaView>
                    <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
                        <Text style={styles.backIcon}>‹</Text>
                        <Text style={styles.backLabel}>Back</Text>
                    </TouchableOpacity>
                </SafeAreaView>
            </TouchableOpacity>

            {/* Bottom sheet card */}
            <View style={[styles.sheet, { maxHeight: sheetMaxHeight }]}>
                <View style={styles.dragHandle} />

                <ScrollView
                    showsVerticalScrollIndicator={false}
                    contentContainerStyle={styles.sheetContent}
                    nestedScrollEnabled
                >
                    <Text style={styles.eventTitle}>{event.title}</Text>

                    {/* Meta */}
                    <View style={styles.metaGroup}>
                        <View style={styles.metaRow}>
                            <Ionicons name="calendar-outline" size={15} color="#000000" />
                            <Text style={styles.metaText}>{event.date}</Text>
                        </View>
                        {event.time ? (
                            <View style={styles.metaRow}>
                                <Ionicons name="time-outline" size={15} color="#000000" />
                                <Text style={styles.metaText}>{event.time}</Text>
                            </View>
                        ) : null}
                        {event.venue ? (
                            <View style={styles.metaRow}>
                                <Ionicons name="location-outline" size={15} color="#000000" />
                                <Text style={styles.metaText}>{event.venue}</Text>
                            </View>
                        ) : null}
                    </View>

                    {/* Organization */}
                    {event.organization && (
                        <TouchableOpacity
                            style={styles.orgRow}
                            onPress={() => navigation.navigate('OrgDetail', { id: event.organization.id })}
                        >
                            {event.organization.logo
                                ? <Image source={{ uri: event.organization.logo }} style={styles.orgLogo} />
                                : <View style={[styles.orgLogo, styles.orgLogoFallback]}>
                                    <Text style={styles.orgLogoText}>{event.organization.name?.[0] ?? 'O'}</Text>
                                  </View>
                            }
                            <Text style={styles.orgName}>{event.organization.name}</Text>
                            <Text style={styles.orgArrow}>›</Text>
                        </TouchableOpacity>
                    )}

                    {/* About */}
                    {event.description ? (
                        <View style={styles.section}>
                            <Text style={styles.sectionLabel}>About This Event</Text>
                            <Text style={styles.bodyText}>{event.description}</Text>
                        </View>
                    ) : null}

                    {/* Benefits */}
                    {event.benefits?.length > 0 ? (
                        <View style={styles.section}>
                            <Text style={styles.sectionLabel}>What you will gain</Text>
                            {event.benefits.map((g, i) => (
                                <View key={i} style={styles.gainRow}>
                                    <View style={styles.gainDot} />
                                    <Text style={styles.gainText}>{g}</Text>
                                </View>
                            ))}
                        </View>
                    ) : null}

                </ScrollView>
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#1e2235' },

    // Dark top area
    backdrop: { flex: 1 },
    backBtn: {
        flexDirection: 'row', alignItems: 'center',
        paddingHorizontal: 16, paddingTop: 8,
    },
    backIcon: { color: '#fff', fontSize: 28, lineHeight: 28, marginRight: 4 },
    backLabel: { color: '#fff', fontSize: 15, fontWeight: '600' },

    // White bottom sheet
    sheet: {
        backgroundColor: '#fff',
        borderTopLeftRadius: 28,
        borderTopRightRadius: 28,
        paddingBottom: 30,
    },
    dragHandle: {
        width: 44, height: 4, borderRadius: 2,
        backgroundColor: '#d1d5db',
        alignSelf: 'center', marginTop: 12, marginBottom: 4,
    },
    sheetContent: { paddingHorizontal: 22, paddingTop: 10, paddingBottom: 20 },

    // Content
    eventTitle: {
        fontSize: 20, fontWeight: '800', color: '#0f2044',
        marginBottom: 14, lineHeight: 28,
    },
    metaGroup: {
        backgroundColor: '#f5f7ff', borderRadius: 12,
        padding: 12, marginBottom: 14, gap: 6,
    },
    metaRow: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    metaText: { fontSize: 13, color: '#334155', flex: 1 },

    orgRow: {
        flexDirection: 'row', alignItems: 'center', gap: 10,
        backgroundColor: '#f0f4ff', borderRadius: 12,
        paddingVertical: 10, paddingHorizontal: 14, marginBottom: 14,
    },
    orgLogo: { width: 34, height: 34, borderRadius: 17 },
    orgLogoFallback: { backgroundColor: '#4A6CF7', alignItems: 'center', justifyContent: 'center' },
    orgLogoText: { color: '#fff', fontSize: 13, fontWeight: '700' },
    orgName: { fontSize: 13, fontWeight: '600', color: '#1e2f6e', flex: 1 },
    orgArrow: { color: '#4A6CF7', fontSize: 20, lineHeight: 24 },

    section: { marginBottom: 14 },
    sectionLabel: {
        fontSize: 15, fontWeight: '700', color: '#0f2044',
        marginBottom: 8,
    },
    bodyText: { fontSize: 13, color: '#555', lineHeight: 21 },
    gainRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 10, marginBottom: 6 },
    gainDot: {
        width: 7, height: 7, borderRadius: 4,
        backgroundColor: '#4A6CF7', marginTop: 6, flexShrink: 0,
    },
    gainText: { flex: 1, fontSize: 13, color: '#444', lineHeight: 21 },
});

import React, { useState, useContext } from 'react';
import {
    View, Text, TextInput, TouchableOpacity,
    StyleSheet, ScrollView, ActivityIndicator, Alert,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { AuthContext } from '../context/AuthContext';
import SelectionModal from '../components/SelectionModal';
import api from '../api/client';

const PROGRAMS = ['BSIT', 'BSCS', 'BSIS', 'BSCpE', 'BSCE', 'BSEE', 'BSME', 'BSN', 'BSBA', 'BSA'];
const YEAR_LEVELS = ['1st', '2nd', '3rd', '4th', '5th'];

const INTERESTS = [
    'Technology', 'Programming', 'Arts', 'Networking', 'Leadership', 'Research', 'Dancing', 'Photography',
    'Gaming', 'Sign Language', 'Photo Video Editing', 'Singing', 'Mental First Aid', 'Acting', 'Innovation',
    'Recording Production', 'Music Publishing',
];
const SKILLS = [
    'Programming', 'Sign Language Fluency', 'Singing', 'Leadership', 'Voice Acting', 'Research Writing', 'Public Speaking',
    'Music Production', 'Stage Performance', 'Strategic Gaming', 'Event Planning', 'Dancing',
];
const ACTIVITIES = [
    'Competition', 'E-Sports Tournament', 'Training', 'Seminar', 'Peer Counseling', 'Public Speaking Event', 'Workshop',
    'Tech Talk', 'Theater Performance', 'Media Production', 'Forum',
];

export default function ProfileAssessmentScreen() {
    const { user, refreshUser } = useContext(AuthContext);
    const isProf = user?.role === 'prof';

    const [first_name, setFirstName]  = useState('');
    const [last_name, setLastName]    = useState('');
    const [department, setDepartment] = useState('');

    // Student-only fields
    const [yearLevel, setYearLevel]   = useState('');
    const [program, setProgram]       = useState('');
    const [interests, setInterests]   = useState([]);
    const [skills, setSkills]         = useState([]);
    const [activities, setActivities] = useState([]);

    const [loading, setLoading] = useState(false);
    const [modal, setModal]     = useState(null);

    const dropdowns = {
        yearLevel:  { label: yearLevel || '' },
        program:    { label: program || 'Select program' },
        interests:  { label: interests.length ? interests.join(', ') : 'Select interest or hobby' },
        skills:     { label: skills.length ? skills.join(', ') : 'Select skills to improve' },
        activities: { label: activities.length ? activities.join(', ') : 'Select preferred activities' },
    };

    const handleSubmit = async () => {
        if (!first_name.trim() || !last_name.trim()) {
            Alert.alert('Incomplete', 'Please enter your name.');
            return;
        }

        if (isProf) {
            if (!department.trim()) {
                Alert.alert('Incomplete', 'Please enter your department.');
                return;
            }
        } else {
            if (!yearLevel || !program || !interests.length || !skills.length || !activities.length) {
                Alert.alert('Incomplete', 'Please fill in all fields.');
                return;
            }
        }

        setLoading(true);
        try {
            if (isProf) {
                await api.post('/profile/complete', { first_name, last_name, department });
            } else {
                const yearNum = YEAR_LEVELS.indexOf(yearLevel) + 1;
                await api.post('/profile/complete', {
                    first_name, last_name,
                    year_level: yearNum,
                    program,
                    interests,
                    skills,
                    activities,
                });
            }
            await refreshUser();
        } catch (err) {
            Alert.alert('Error', err.message);
        } finally {
            setLoading(false);
        }
    };

    const renderDropdown = (key, placeholder) => (
        <TouchableOpacity style={styles.dropdown} onPress={() => setModal(key)}>
            <Text style={[styles.dropdownText, dropdowns[key].label === placeholder && styles.placeholder]}>
                {dropdowns[key].label || placeholder}
            </Text>
            <Text style={styles.chevron}>▾</Text>
        </TouchableOpacity>
    );

    return (
        <View style={styles.root}>
            <View style={styles.header}>
                <SafeAreaView>
                    <View style={styles.headerInner}>
                        <Text style={styles.headerIcon}>{isProf ? '🎓' : '👤'}</Text>
                        <View>
                            <Text style={styles.headerTitle}>Profile Setup</Text>
                            <Text style={styles.headerSub}>
                                {isProf ? 'Tell us about yourself (Professor)' : 'Tell us about yourself'}
                            </Text>
                        </View>
                    </View>
                </SafeAreaView>
            </View>

            <ScrollView style={styles.scroll} contentContainerStyle={styles.form} keyboardShouldPersistTaps="handled">
                <View style={styles.row}>
                    <View style={[styles.fieldWrap, { flex: 1 }]}>
                        <Text style={styles.label}>First Name</Text>
                        <TextInput
                            style={styles.textInput}
                            placeholder="Your first name"
                            placeholderTextColor="#bbb"
                            value={first_name}
                            onChangeText={setFirstName}
                        />
                    </View>
                    <View style={[styles.fieldWrap, { flex: 1 }]}>
                        <Text style={styles.label}>Last Name</Text>
                        <TextInput
                            style={styles.textInput}
                            placeholder="Your last name"
                            placeholderTextColor="#bbb"
                            value={last_name}
                            onChangeText={setLastName}
                        />
                    </View>
                </View>

                {isProf ? (
                    <View style={styles.fieldWrap}>
                        <Text style={styles.label}>Department</Text>
                        <TextInput
                            style={styles.textInput}
                            placeholder="e.g. College of Information Technology"
                            placeholderTextColor="#bbb"
                            value={department}
                            onChangeText={setDepartment}
                        />
                    </View>
                ) : (
                    <>
                        <View style={styles.row}>
                            <View style={[styles.fieldWrap, { flex: 1 }]}>
                                <Text style={styles.label}>Program</Text>
                                {renderDropdown('program', 'Select program')}
                            </View>
                            <View style={[styles.fieldWrap, { flex: 0.25 }]}>
                                <Text style={styles.label}>Year</Text>
                                {renderDropdown('yearLevel', '')}
                            </View>
                        </View>

                        <View style={styles.fieldWrap}>
                            <Text style={styles.label}>Interest or Hobby</Text>
                            {renderDropdown('interests', 'Select interest or hobby')}
                        </View>

                        <View style={styles.fieldWrap}>
                            <Text style={styles.label}>Skills to improve</Text>
                            {renderDropdown('skills', 'Select skills to improve')}
                        </View>

                        <View style={styles.fieldWrap}>
                            <Text style={styles.label}>Preferred Activities</Text>
                            {renderDropdown('activities', 'Select preferred activities')}
                        </View>
                    </>
                )}

                <TouchableOpacity
                    style={[styles.nextBtn, loading && { opacity: 0.7 }]}
                    onPress={handleSubmit}
                    disabled={loading}
                >
                    {loading
                        ? <ActivityIndicator color="#fff" />
                        : <Text style={styles.nextBtnText}>Continue</Text>
                    }
                </TouchableOpacity>
            </ScrollView>

            {modal === 'yearLevel' && (
                <SelectionModal
                    visible title="Year Level" subtitle="Select your year level"
                    options={YEAR_LEVELS} selected={yearLevel ? [yearLevel] : []} max={1}
                    onConfirm={(vals) => { setYearLevel(vals[0] || ''); setModal(null); }}
                    onCancel={() => setModal(null)}
                />
            )}
            {modal === 'program' && (
                <SelectionModal
                    visible title="Program" subtitle="Select your program"
                    options={PROGRAMS} selected={program ? [program] : []} max={1}
                    onConfirm={(vals) => { setProgram(vals[0] || ''); setModal(null); }}
                    onCancel={() => setModal(null)}
                />
            )}
            {modal === 'interests' && (
                <SelectionModal
                    visible title="Interest & Hobbies" subtitle="Select up to 3"
                    options={INTERESTS} selected={interests} max={3}
                    onConfirm={(vals) => { setInterests(vals); setModal(null); }}
                    onCancel={() => setModal(null)}
                />
            )}
            {modal === 'skills' && (
                <SelectionModal
                    visible title="Skills to improve" subtitle="Select up to 3"
                    options={SKILLS} selected={skills} max={3}
                    onConfirm={(vals) => { setSkills(vals); setModal(null); }}
                    onCancel={() => setModal(null)}
                />
            )}
            {modal === 'activities' && (
                <SelectionModal
                    visible title="Preferred Activities" subtitle="Select up to 3"
                    options={ACTIVITIES} selected={activities} max={3}
                    onConfirm={(vals) => { setActivities(vals); setModal(null); }}
                    onCancel={() => setModal(null)}
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#f5f6fa' },
    header: { backgroundColor: '#4A6CF7' },
    headerInner: {
        flexDirection: 'row', alignItems: 'center', gap: 12,
        padding: 20, paddingTop: 16,
    },
    headerIcon: { fontSize: 28 },
    headerTitle: { fontSize: 20, fontWeight: '700', color: '#fff' },
    headerSub: { fontSize: 13, color: 'rgba(255,255,255,0.75)' },
    scroll: { flex: 1 },
    form: { padding: 20, gap: 16, paddingBottom: 40 },
    row: { flexDirection: 'row', gap: 12 },
    fieldWrap: {},
    label: { fontSize: 13, fontWeight: '600', color: '#333', marginBottom: 6 },
    textInput: {
        backgroundColor: '#fff',
        borderRadius: 10,
        paddingHorizontal: 14,
        height: 48,
        fontSize: 14,
        color: '#333',
        borderWidth: 1,
        borderColor: '#e8e8e8',
    },
    dropdown: {
        backgroundColor: '#fff',
        borderRadius: 10,
        paddingHorizontal: 14,
        height: 48,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        borderWidth: 1,
        borderColor: '#e8e8e8',
    },
    dropdownText: { fontSize: 14, color: '#333', flex: 1 },
    placeholder: { color: '#bbb' },
    chevron: { color: '#888', fontSize: 16 },
    nextBtn: {
        backgroundColor: '#1e3a8a',
        borderRadius: 28,
        height: 52,
        alignItems: 'center',
        justifyContent: 'center',
        marginTop: 8,
    },
    nextBtnText: { color: '#fff', fontSize: 16, fontWeight: '700' },
});

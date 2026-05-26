import React, { useState, useContext } from 'react';
import {
    View, Text, TextInput, TouchableOpacity,
    StyleSheet, Image, KeyboardAvoidingView,
    Platform, ScrollView, ActivityIndicator, Alert,
    useWindowDimensions,
} from 'react-native';

const APP_LOGO = require('../../assets/orgfinder-logo.png');
import { SafeAreaView } from 'react-native-safe-area-context';
import { AuthContext } from '../context/AuthContext';

export default function LoginScreen() {
    const { login } = useContext(AuthContext);
    const { width, height } = useWindowDimensions();
    const [email, setEmail]       = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading]   = useState(false);

    const helloSize   = Math.min(Math.round(width * 0.12), 52);
    const titleSize   = Math.min(Math.round(width * 0.072), 28);
    const inputHeight = Math.max(Math.round(height * 0.065), 48);

    const handleLogin = async () => {
        if (!email.trim() || !password.trim()) {
            Alert.alert('Error', 'Please enter your email and password.');
            return;
        }
        setLoading(true);
        try {
            await login(email.trim(), password);
        } catch (err) {
            Alert.alert('Login Failed', err.message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <View style={styles.root}>
            {/* Blue top section */}
            <View style={styles.topSection}>
                <SafeAreaView>
                    <Text style={[styles.hello, { fontSize: helloSize }]}>Hello!</Text>
                    <Text style={styles.helloSub}>Welcome to NEUORG</Text>
                </SafeAreaView>
            </View>

            {/* White card */}
            <KeyboardAvoidingView
                behavior={Platform.OS === 'ios' ? 'padding' : undefined}
                style={styles.cardWrap}
            >
                <ScrollView contentContainerStyle={styles.card} keyboardShouldPersistTaps="handled">
                    <Text style={[styles.loginTitle, { fontSize: titleSize }]}>Login</Text>

                    <View style={[styles.inputWrap, { height: inputHeight }]}>
                        <Text style={styles.inputIcon}>@</Text>
                        <TextInput
                            style={styles.input}
                            placeholder="Institutional Email"
                            placeholderTextColor="#aaa"
                            keyboardType="email-address"
                            autoCapitalize="none"
                            value={email}
                            onChangeText={setEmail}
                        />
                    </View>

                    <View style={[styles.inputWrap, { height: inputHeight }]}>
                        <Text style={styles.inputIcon}>•••</Text>
                        <TextInput
                            style={styles.input}
                            placeholder="Password"
                            placeholderTextColor="#aaa"
                            secureTextEntry
                            value={password}
                            onChangeText={setPassword}
                        />
                    </View>

                    <TouchableOpacity style={styles.forgotWrap}>
                        <Text style={styles.forgotText}>Forgot password?</Text>
                    </TouchableOpacity>

                    <TouchableOpacity
                        style={[styles.loginBtn, { height: inputHeight }, loading && { opacity: 0.7 }]}
                        onPress={handleLogin}
                        disabled={loading}
                    >
                        {loading
                            ? <ActivityIndicator color="#fff" />
                            : <Text style={styles.loginBtnText}>Login</Text>
                        }
                    </TouchableOpacity>
                </ScrollView>
            </KeyboardAvoidingView>
        </View>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#4A6CF7' },
    topSection: {
        flex: 1,
        paddingHorizontal: 30,
        paddingTop: 20,
        justifyContent: 'flex-end',
        paddingBottom: 40,
    },
    hello: {
        fontWeight: '800',
        color: '#fff',
        marginBottom: 4,
    },
    helloSub: {
        fontWeight: '400',
        fontSize: 14,
        color: 'rgba(255,255,255,0.85)',
        marginBottom: 10,
    },
    brandRow: {
        flexDirection: 'row',
        alignItems: 'center',
    },
    brandText: {
        fontSize: 38,
        fontWeight: '800',
        color: '#fff',
        letterSpacing: 1,
    },
    brandLogo: {},
    cardWrap: {
        backgroundColor: '#fff',
        borderTopLeftRadius: 32,
        borderTopRightRadius: 32,
    },
    card: {
        padding: 30,
        paddingTop: 36,
        paddingBottom: 50,
    },
    loginTitle: {
        fontWeight: '700',
        color: '#1e2f6e',
        marginBottom: 24,
    },
    inputWrap: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f5f5f5',
        borderRadius: 12,
        paddingHorizontal: 14,
        marginBottom: 14,
    },
    inputIcon: { fontSize: 14, marginRight: 10, color: '#888', fontWeight: '600' },
    input: { flex: 1, fontSize: 15, color: '#333' },
    forgotWrap: { alignItems: 'flex-end', marginBottom: 24 },
    forgotText: { fontSize: 13, color: '#4A6CF7' },
    loginBtn: {
        backgroundColor: '#1e3a8a',
        borderRadius: 28,
        alignItems: 'center',
        justifyContent: 'center',
    },
    loginBtnText: { color: '#fff', fontSize: 16, fontWeight: '700' },
});

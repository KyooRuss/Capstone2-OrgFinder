import React, { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Animated, Image, useWindowDimensions } from 'react-native';

export default function SplashScreen() {
    const { width } = useWindowDimensions();
    const scale   = useRef(new Animated.Value(0.7)).current;
    const opacity = useRef(new Animated.Value(0)).current;

    const logoSize  = Math.min(Math.round(width * 0.27), 110);
    const titleSize = Math.min(Math.round(width * 0.075), 30);

    useEffect(() => {
        Animated.parallel([
            Animated.spring(scale, { toValue: 1, useNativeDriver: true }),
            Animated.timing(opacity, { toValue: 1, duration: 600, useNativeDriver: true }),
        ]).start();
    }, []);

    return (
        <View style={styles.container}>
            <Animated.View style={{ transform: [{ scale }], opacity, alignItems: 'center' }}>
                <Image
                    source={require('../../assets/orgfinder-logo.png')}
                    style={{ width: logoSize, height: logoSize, marginBottom: 16 }}
                    resizeMode="contain"
                />
                <Text style={[styles.title, { fontSize: titleSize }]}>NEUORG</Text>
            </Animated.View>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#4A6CF7',
        alignItems: 'center',
        justifyContent: 'center',
    },
    title: {
        color: '#fff',
        fontWeight: '800',
        letterSpacing: 4,
        textAlign: 'center',
    },
});

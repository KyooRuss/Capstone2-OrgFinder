import React, { useContext, useState, useEffect } from 'react';
import { View, Text, StyleSheet, AppState } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { AuthContext } from '../context/AuthContext';
import api from '../api/client';

import SplashScreen        from '../screens/SplashScreen';
import LoginScreen         from '../screens/LoginScreen';
import ProfileAssessment   from '../screens/ProfileAssessmentScreen';
import HomeScreen          from '../screens/HomeScreen';
import ProfileScreen       from '../screens/ProfileScreen';
import EditProfileScreen   from '../screens/EditProfileScreen';
import OrgsScreen          from '../screens/OrganizationsScreen';
import RecruitmentScreen   from '../screens/RecruitmentScreen';
import OrgDetailScreen     from '../screens/OrgDetailScreen';
import EventsScreen        from '../screens/EventsScreen';
import EventDetailScreen   from '../screens/EventDetailScreen';
import NotificationsScreen from '../screens/NotificationsScreen';
import OrgChatScreen       from '../screens/OrgChatScreen';
import MyChatsScreen       from '../screens/MyChatsScreen';

const Stack = createNativeStackNavigator();
const Tab   = createBottomTabNavigator();

// Styles must be defined before any component that references them
const tabStyles = StyleSheet.create({
    bar: {
        position: 'absolute',
        bottom: 14,
        left: 20,
        right: 20,
        height: 58,
        borderRadius: 24,
        backgroundColor: '#fff',
        borderTopWidth: 0,
        elevation: 14,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.12,
        shadowRadius: 14,
        paddingBottom: 0,
        paddingTop: 0,
    },
    iconWrap: {
        width: 44, height: 44, borderRadius: 22,
        alignItems: 'center', justifyContent: 'center',
    },
    iconWrapActive: {
        backgroundColor: '#4A6CF7',
        shadowColor: '#4A6CF7',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.35,
        shadowRadius: 8,
        elevation: 6,
    },
    badge: {
        position: 'absolute', top: 2, right: 2,
        backgroundColor: '#ef4444',
        borderRadius: 9, minWidth: 16, height: 16,
        alignItems: 'center', justifyContent: 'center',
        paddingHorizontal: 3,
    },
    badgeText: { color: '#fff', fontSize: 9, fontWeight: '800' },
});

const TAB_SCREEN_OPTIONS = {
    headerShown: false,
    tabBarShowLabel: false,
    tabBarStyle: tabStyles.bar,
    tabBarActiveTintColor:   '#4A6CF7',
    tabBarInactiveTintColor: '#94a3b8',
};

function TabIcon({ iconName, focused, color, badge }) {
    return (
        <View style={[tabStyles.iconWrap, focused && tabStyles.iconWrapActive]}>
            <Ionicons name={iconName} size={22} color={focused ? '#fff' : '#94a3b8'} />
            {badge > 0 && (
                <View style={tabStyles.badge}>
                    <Text style={tabStyles.badgeText}>{badge > 99 ? '99+' : badge}</Text>
                </View>
            )}
        </View>
    );
}

function useUnreadNotifs() {
    const { refreshUser } = useContext(AuthContext);
    const [unreadNotifs, setUnreadNotifs] = useState(0);

    const fetchUnread = async () => {
        try {
            const res = await api.get('/notifications/unread-count');
            setUnreadNotifs(res.data.unread_count ?? 0);
        } catch {}
    };

    useEffect(() => {
        fetchUnread();
        const interval = setInterval(fetchUnread, 60000);
        const sub = AppState.addEventListener('change', (state) => {
            if (state === 'active') {
                fetchUnread();
                refreshUser().catch(() => {});
            }
        });
        return () => { clearInterval(interval); sub.remove(); };
    }, []);

    return { unreadNotifs, fetchUnread };
}

function ProfTabs() {
    const { unreadNotifs, fetchUnread } = useUnreadNotifs();
    return (
        <Tab.Navigator initialRouteName="Home" screenOptions={TAB_SCREEN_OPTIONS}>
            <Tab.Screen
                name="Orgs"
                component={OrgsScreen}
                options={{ tabBarIcon: ({ focused, color }) => (
                    <TabIcon iconName={focused ? 'people' : 'people-outline'} focused={focused} color={color} />
                )}}
            />
            <Tab.Screen
                name="Home"
                component={HomeScreen}
                options={{ tabBarIcon: ({ focused, color }) => (
                    <TabIcon iconName={focused ? 'home' : 'home-outline'} focused={focused} color={color} />
                )}}
            />
            <Tab.Screen
                name="Events"
                component={EventsScreen}
                options={{ tabBarIcon: ({ focused, color }) => (
                    <TabIcon iconName={focused ? 'calendar' : 'calendar-outline'} focused={focused} color={color} />
                )}}
            />
            <Tab.Screen
                name="Notifications"
                component={NotificationsScreen}
                listeners={{ focus: () => fetchUnread() }}
                options={{ tabBarIcon: ({ focused, color }) => (
                    <TabIcon iconName={focused ? 'notifications' : 'notifications-outline'} focused={focused} color={color} badge={unreadNotifs} />
                )}}
            />
        </Tab.Navigator>
    );
}

function StudentTabs() {
    const { unreadNotifs, fetchUnread } = useUnreadNotifs();
    return (
        <Tab.Navigator initialRouteName="Home" screenOptions={TAB_SCREEN_OPTIONS}>
            <Tab.Screen
                name="Orgs"
                component={OrgsScreen}
                options={{ tabBarIcon: ({ focused, color }) => (
                    <TabIcon iconName={focused ? 'people' : 'people-outline'} focused={focused} color={color} />
                )}}
            />
            <Tab.Screen
                name="Recruitment"
                component={RecruitmentScreen}
                options={{ tabBarIcon: ({ focused, color }) => (
                    <TabIcon iconName={focused ? 'id-card' : 'id-card-outline'} focused={focused} color={color} />
                )}}
            />
            <Tab.Screen
                name="Home"
                component={HomeScreen}
                options={{ tabBarIcon: ({ focused, color }) => (
                    <TabIcon iconName={focused ? 'home' : 'home-outline'} focused={focused} color={color} />
                )}}
            />
            <Tab.Screen
                name="Events"
                component={EventsScreen}
                options={{ tabBarIcon: ({ focused, color }) => (
                    <TabIcon iconName={focused ? 'calendar' : 'calendar-outline'} focused={focused} color={color} />
                )}}
            />
            <Tab.Screen
                name="Notifications"
                component={NotificationsScreen}
                listeners={{ focus: () => fetchUnread() }}
                options={{ tabBarIcon: ({ focused, color }) => (
                    <TabIcon iconName={focused ? 'notifications' : 'notifications-outline'} focused={focused} color={color} badge={unreadNotifs} />
                )}}
            />
        </Tab.Navigator>
    );
}

function MainTabs() {
    const { user } = useContext(AuthContext);
    return user?.role === 'prof' ? <ProfTabs /> : <StudentTabs />;
}

export default function AppNavigator() {
    const { token, user, loading } = useContext(AuthContext);

    if (loading) return <SplashScreen />;

    return (
        <NavigationContainer>
            <Stack.Navigator screenOptions={{ headerShown: false }}>
                {!token ? (
                    <Stack.Screen name="Login" component={LoginScreen} />
                ) : !user?.profile_completed ? (
                    <Stack.Screen name="ProfileAssessment" component={ProfileAssessment} />
                ) : (
                    <>
                        <Stack.Screen name="MainTabs"    component={MainTabs} />
                        <Stack.Screen name="Profile"     component={ProfileScreen} />
                        <Stack.Screen name="EditProfile" component={EditProfileScreen} />
                        <Stack.Screen name="OrgDetail"   component={OrgDetailScreen} />
                        <Stack.Screen name="EventDetail" component={EventDetailScreen} />
                        <Stack.Screen name="OrgChat"     component={OrgChatScreen} />
                        <Stack.Screen name="MyChats"     component={MyChatsScreen} />
                    </>
                )}
            </Stack.Navigator>
        </NavigationContainer>
    );
}

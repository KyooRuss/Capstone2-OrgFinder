import React, { useContext } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { AuthContext } from '../context/AuthContext';

import SplashScreen      from '../screens/SplashScreen';
import LoginScreen       from '../screens/LoginScreen';
import ProfileAssessment from '../screens/ProfileAssessmentScreen';
import HomeScreen        from '../screens/HomeScreen';
import ProfileScreen     from '../screens/ProfileScreen';
import EditProfileScreen from '../screens/EditProfileScreen';
import OrgsScreen        from '../screens/OrganizationsScreen';
import OrgDetailScreen   from '../screens/OrgDetailScreen';
import EventsScreen      from '../screens/EventsScreen';
import EventDetailScreen from '../screens/EventDetailScreen';
import OrgChatScreen     from '../screens/OrgChatScreen';
import MyChatsScreen     from '../screens/MyChatsScreen';

const Stack = createNativeStackNavigator();

export default function AppNavigator() {
    const { token, user, loading } = useContext(AuthContext);

    if (loading) return <SplashScreen />;

    return (
        <NavigationContainer>
            <Stack.Navigator screenOptions={{ headerShown: false }}>
                {!token ? (
                    <>
                        <Stack.Screen name="Login"      component={LoginScreen} />
                    </>
                ) : !user?.profile_completed ? (
                    <>
                        <Stack.Screen name="ProfileAssessment" component={ProfileAssessment} />
                    </>
                ) : (
                    <>
                        <Stack.Screen name="Home"        component={HomeScreen} />
                        <Stack.Screen name="Profile"     component={ProfileScreen} />
                        <Stack.Screen name="EditProfile" component={EditProfileScreen} />
                        <Stack.Screen name="Orgs"        component={OrgsScreen} />
                        <Stack.Screen name="OrgDetail"   component={OrgDetailScreen} />
                        <Stack.Screen name="Events"      component={EventsScreen} />
                        <Stack.Screen name="EventDetail" component={EventDetailScreen} />
                        <Stack.Screen name="OrgChat"     component={OrgChatScreen} />
                        <Stack.Screen name="MyChats"     component={MyChatsScreen} />
                    </>
                )}
            </Stack.Navigator>
        </NavigationContainer>
    );
}

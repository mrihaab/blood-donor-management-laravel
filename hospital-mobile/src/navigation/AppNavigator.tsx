import React from "react";
import { NavigationContainer } from "@react-navigation/native";
import { createNativeStackNavigator } from "@react-navigation/native-stack";
import { useAuth } from "../auth/AuthContext";
import { SplashScreen } from "../screens/SplashScreen";
import { LoginScreen } from "../screens/LoginScreen";
import { DashboardScreen } from "../screens/DashboardScreen";
import { PatientListScreen } from "../screens/PatientListScreen";
import { PatientDetailScreen } from "../screens/PatientDetailScreen";
import { PatientFormScreen } from "../screens/PatientFormScreen";
import { RequisitionListScreen } from "../screens/RequisitionListScreen";
import { RequisitionDetailScreen } from "../screens/RequisitionDetailScreen";
import { RequisitionCreateScreen } from "../screens/RequisitionCreateScreen";
import { RootStackParamList } from "./types";

const Stack = createNativeStackNavigator<RootStackParamList>();

export const AppNavigator: React.FC = () => {
  const { authState } = useAuth();

  if (authState === "bootstrapping") {
    return <SplashScreen />;
  }

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        {authState === "authenticated" ? (
          <>
            <Stack.Screen name="Dashboard" component={DashboardScreen} options={{ title: "Dashboard" }} />
            <Stack.Screen name="PatientList" component={PatientListScreen} options={{ title: "Patients" }} />
            <Stack.Screen name="PatientDetail" component={PatientDetailScreen} options={{ title: "Patient Details" }} />
            <Stack.Screen name="PatientCreate" component={PatientFormScreen} options={{ title: "Register Patient" }} />
            <Stack.Screen name="PatientEdit" component={PatientFormScreen} options={{ title: "Edit Patient" }} />
            <Stack.Screen name="RequisitionList" component={RequisitionListScreen} options={{ title: "Requisitions" }} />
            <Stack.Screen name="RequisitionDetail" component={RequisitionDetailScreen} options={{ title: "Requisition Details" }} />
            <Stack.Screen name="RequisitionCreate" component={RequisitionCreateScreen} options={{ title: "Create Requisition" }} />
          </>
        ) : (
          <Stack.Screen name="Login" component={LoginScreen} />
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
};
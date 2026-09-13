import React, { useEffect } from "react";
import { SafeAreaProvider } from "react-native-safe-area-context";
import { QueryClientProvider } from "@tanstack/react-query";
import { StatusBar } from "expo-status-bar";
import * as SplashScreen from "expo-splash-screen";
import { AuthProvider } from "./src/auth/AuthContext";
import { queryClient } from "./src/api/queryClient";
import { AppNavigator } from "./src/navigation/AppNavigator";

export default function App() {
  useEffect(() => {
    SplashScreen.hideAsync().catch(() => {});
  }, []);



  return (
    <SafeAreaProvider>
      <QueryClientProvider client={queryClient}>
        <AuthProvider>
          <StatusBar style="dark" />
          <AppNavigator />
        </AuthProvider>
      </QueryClientProvider>
    </SafeAreaProvider>
  );
}
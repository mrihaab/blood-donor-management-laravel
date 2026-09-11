import React from "react";
import { View, Text, StyleSheet, ActivityIndicator } from "react-native";
import { colors, spacing } from "../theme";

export const SplashScreen: React.FC = () => (
  <View style={styles.container}>
    <Text style={styles.title}>🏥 Hospital Portal</Text>
    <ActivityIndicator size="large" color={colors.primary} style={styles.loader} />
    <Text style={styles.subtitle}>Initializing Secure Session...</Text>
  </View>
);

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: "center", alignItems: "center", backgroundColor: colors.background },
  title: { fontSize: 24, fontWeight: "bold", color: colors.textPrimary },
  loader: { marginVertical: spacing.md },
  subtitle: { fontSize: 14, color: colors.textSecondary },
});
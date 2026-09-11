import React from "react";
import { View, Text, StyleSheet, Button } from "react-native";
import { colors, spacing } from "../theme";
import { useAuth } from "../auth/AuthContext";

export const DashboardScreen: React.FC = () => {
  const { setUnauthenticated } = useAuth();

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Hospital Dashboard</Text>
      <Text style={styles.subtitle}>Stage A Authenticated Screen Foundation</Text>
      <View style={styles.buttonWrapper}>
        <Button title="Logout This Device" color={colors.primary} onPress={setUnauthenticated} />
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: "center", alignItems: "center", backgroundColor: colors.background, padding: spacing.md },
  title: { fontSize: 22, fontWeight: "bold", color: colors.textPrimary },
  subtitle: { fontSize: 14, color: colors.textSecondary, marginBottom: spacing.lg },
  buttonWrapper: { width: "80%" },
});
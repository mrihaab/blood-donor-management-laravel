import React from "react";
import { View, Text, StyleSheet, Button } from "react-native";
import { colors, spacing } from "../theme";
import { useAuth } from "../auth/AuthContext";

export const LoginScreen: React.FC = () => {
  const { setAuthenticated } = useAuth();

  const handleMockLogin = async () => {
    await setAuthenticated("stage_a_mock_bearer_token_12345");
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Clinical Operations Portal</Text>
      <Text style={styles.subtitle}>Stage A Placeholder Login Screen</Text>
      <View style={styles.buttonWrapper}>
        <Button title="Mock Stage A Login" color={colors.primary} onPress={handleMockLogin} />
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: "center", alignItems: "center", backgroundColor: colors.background, padding: spacing.md },
  title: { fontSize: 20, fontWeight: "bold", color: colors.textPrimary },
  subtitle: { fontSize: 14, color: colors.textSecondary, marginBottom: spacing.lg },
  buttonWrapper: { width: "80%" },
});
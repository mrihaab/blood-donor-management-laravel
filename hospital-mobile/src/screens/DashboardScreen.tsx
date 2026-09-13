import React, { useState } from "react";
import { View, Text, StyleSheet, TouchableOpacity, ActivityIndicator } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { colors, spacing } from "../theme";
import { useAuth } from "../auth/AuthContext";
import { getSafeErrorMessage } from "../api/authApi";

export const DashboardScreen: React.FC = () => {
  const { user, logout } = useAuth();
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const handleLogout = async () => {
    if (isLoggingOut) return;

    setIsLoggingOut(true);
    setErrorMessage(null);

    try {
      await logout();
    } catch (err) {
      const safeMsg = getSafeErrorMessage(err, "general");
      setErrorMessage(safeMsg);
    } finally {
      setIsLoggingOut(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.card}>
        <Text style={styles.title}>Hospital Dashboard</Text>
        <Text style={styles.subtitle}>Authenticated Operations Session</Text>

        {errorMessage ? (
          <View style={styles.errorBanner} accessibilityRole="alert" testID="dashboard-error-banner">
            <Text style={styles.errorText}>{errorMessage}</Text>
          </View>
        ) : null}

        <View style={styles.infoGroup}>
          <Text style={styles.infoLabel}>Staff Member</Text>
          <Text style={styles.infoValue} testID="user-name">{user?.name || "N/A"}</Text>
        </View>

        <View style={styles.infoGroup}>
          <Text style={styles.infoLabel}>Email</Text>
          <Text style={styles.infoValue} testID="user-email">{user?.email || "N/A"}</Text>
        </View>

        <View style={styles.infoGroup}>
          <Text style={styles.infoLabel}>Hospital Facility</Text>
          <Text style={styles.infoValue} testID="hospital-name">{user?.hospital?.name || "N/A"}</Text>
        </View>

        <View style={styles.infoGroup}>
          <Text style={styles.infoLabel}>License Number</Text>
          <Text style={styles.infoValue} testID="hospital-license">{user?.hospital?.license_number || "N/A"}</Text>
        </View>

        <TouchableOpacity
          style={[styles.logoutButton, isLoggingOut && styles.logoutButtonDisabled]}
          onPress={handleLogout}
          disabled={isLoggingOut}
          accessibilityRole="button"
          accessibilityLabel="Logout This Device"
          testID="logout-button"
        >
          {isLoggingOut ? (
            <ActivityIndicator color="#FFFFFF" testID="logout-loading-indicator" />
          ) : (
            <Text style={styles.logoutButtonText}>Logout This Device</Text>
          )}
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    backgroundColor: colors.background,
    padding: spacing.md,
  },
  card: {
    width: "100%",
    maxWidth: 400,
    backgroundColor: "#FFFFFF",
    borderRadius: 12,
    padding: spacing.lg,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 3,
  },
  title: {
    fontSize: 22,
    fontWeight: "bold",
    color: colors.textPrimary,
    marginBottom: spacing.xs,
    textAlign: "center",
  },
  subtitle: {
    fontSize: 14,
    color: colors.textSecondary,
    marginBottom: spacing.lg,
    textAlign: "center",
  },
  errorBanner: {
    backgroundColor: "#FEE2E2",
    borderWidth: 1,
    borderColor: "#EF4444",
    borderRadius: 8,
    padding: spacing.sm,
    marginBottom: spacing.md,
  },
  errorText: {
    color: "#B91C1C",
    fontSize: 14,
    textAlign: "center",
  },
  infoGroup: {
    marginBottom: spacing.md,
    paddingBottom: spacing.xs,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  infoLabel: {
    fontSize: 12,
    color: colors.textSecondary,
    textTransform: "uppercase",
    fontWeight: "600",
    marginBottom: 2,
  },
  infoValue: {
    fontSize: 16,
    color: colors.textPrimary,
    fontWeight: "500",
  },
  logoutButton: {
    height: 48,
    backgroundColor: colors.primary,
    borderRadius: 8,
    justifyContent: "center",
    alignItems: "center",
    marginTop: spacing.md,
  },
  logoutButtonDisabled: {
    opacity: 0.6,
  },
  logoutButtonText: {
    color: "#FFFFFF",
    fontSize: 16,
    fontWeight: "bold",
  },
});
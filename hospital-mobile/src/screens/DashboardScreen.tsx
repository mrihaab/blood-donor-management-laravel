import React, { useState } from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  ScrollView,
  RefreshControl,
} from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { colors, spacing } from "../theme";
import { useAuth } from "../auth/AuthContext";
import { getSafeErrorMessage } from "../api/authApi";
import { useDashboard } from "../api/useDashboard";
import { DashboardRequisition } from "../api/dashboardApi";

export const DashboardScreen: React.FC = () => {
  const { user, logout } = useAuth();
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [logoutError, setLogoutError] = useState<string | null>(null);

  const {
    data: dashboardData,
    isLoading,
    isError,
    error,
    refetch,
    isRefetching,
  } = useDashboard();

  const handleLogout = async () => {
    if (isLoggingOut) return;

    setIsLoggingOut(true);
    setLogoutError(null);

    try {
      await logout();
    } catch (err) {
      const safeMsg = getSafeErrorMessage(err, "general");
      setLogoutError(safeMsg);
    } finally {
      setIsLoggingOut(false);
    }
  };

  const hospitalName = dashboardData?.hospital?.name || user?.hospital?.name || "N/A";
  const hospitalLicense = dashboardData?.hospital?.license_number || user?.hospital?.license_number || "N/A";
  const hospitalCity = dashboardData?.hospital?.city || "N/A";
  const hospitalStatus = (dashboardData?.hospital?.status || user?.hospital?.status || "N/A").toUpperCase();

  const kpis = dashboardData?.kpis;
  const recentRequisitions = dashboardData?.recent_requisitions || [];

  const displayErrorMessage = logoutError || (isError ? getSafeErrorMessage(error, "general") : null);

  const getUrgencyBadgeStyle = (urgency: string) => {
    const u = urgency.toLowerCase();
    if (u === "critical" || u === "urgent" || u === "emergency") {
      return { container: styles.badgeDanger, text: styles.badgeTextDanger };
    }
    if (u === "high") {
      return { container: styles.badgeWarning, text: styles.badgeTextWarning };
    }
    return { container: styles.badgeInfo, text: styles.badgeTextInfo };
  };

  const getStatusBadgeStyle = (status: string) => {
    const s = status.toLowerCase();
    if (s === "approved" || s === "dispensed" || s === "completed" || s === "active") {
      return { container: styles.badgeSuccess, text: styles.badgeTextSuccess };
    }
    if (s === "pending") {
      return { container: styles.badgeWarning, text: styles.badgeTextWarning };
    }
    if (s === "rejected" || s === "cancelled") {
      return { container: styles.badgeDanger, text: styles.badgeTextDanger };
    }
    return { container: styles.badgeInfo, text: styles.badgeTextInfo };
  };

  return (
    <SafeAreaView style={styles.container} testID="dashboard-screen">
      <ScrollView
        style={styles.scrollContainer}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl
            refreshing={isRefetching}
            onRefresh={refetch}
            colors={[colors.primary]}
            tintColor={colors.primary}
          />
        }
      >
        {/* Header Section */}
        <View style={styles.headerContainer}>
          <Text style={styles.appTitle}>Hospital Operations</Text>
          <Text style={styles.subtitle}>Authenticated Dashboard</Text>
        </View>

        {/* Global Error Banner */}
        {displayErrorMessage ? (
          <View style={styles.errorBanner} accessibilityRole="alert" testID="dashboard-error-banner">
            <Text style={styles.errorText}>{displayErrorMessage}</Text>
            {isError && !logoutError ? (
              <TouchableOpacity
                style={styles.retryButton}
                onPress={() => refetch()}
                accessibilityRole="button"
                accessibilityLabel="Retry loading dashboard"
                testID="dashboard-retry-button"
              >
                <Text style={styles.retryButtonText}>Retry</Text>
              </TouchableOpacity>
            ) : null}
          </View>
        ) : null}

        {/* Loading Indicator for initial fetch */}
        {isLoading && !dashboardData ? (
          <View style={styles.loadingCard} testID="dashboard-loading">
            <ActivityIndicator size="large" color={colors.primary} />
            <Text style={styles.loadingText}>Loading operations dashboard...</Text>
          </View>
        ) : null}

        {/* Hospital & Staff Profile Card */}
        <View style={styles.card}>
          <View style={styles.cardHeaderRow}>
            <Text style={styles.cardTitle}>Hospital Facility Profile</Text>
            <View style={[styles.badgeContainer, getStatusBadgeStyle(hospitalStatus).container]}>
              <Text style={[styles.badgeText, getStatusBadgeStyle(hospitalStatus).text]} testID="hospital-status">
                {hospitalStatus}
              </Text>
            </View>
          </View>

          <View style={styles.infoGrid}>
            <View style={styles.infoGroup}>
              <Text style={styles.infoLabel}>Facility Name</Text>
              <Text style={styles.infoValue} testID="hospital-name">{hospitalName}</Text>
            </View>

            <View style={styles.infoGroup}>
              <Text style={styles.infoLabel}>License Number</Text>
              <Text style={styles.infoValue} testID="hospital-license">{hospitalLicense}</Text>
            </View>

            <View style={styles.infoGroup}>
              <Text style={styles.infoLabel}>City / Region</Text>
              <Text style={styles.infoValue} testID="hospital-city">{hospitalCity}</Text>
            </View>

            <View style={styles.infoGroup}>
              <Text style={styles.infoLabel}>Staff Member</Text>
              <Text style={styles.infoValue} testID="user-name">{user?.name || "N/A"}</Text>
            </View>

            <View style={styles.infoGroup}>
              <Text style={styles.infoLabel}>Staff Email</Text>
              <Text style={styles.infoValue} testID="user-email">{user?.email || "N/A"}</Text>
            </View>
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

        {/* Key Performance Indicators (KPIs) */}
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Operations Overview</Text>
        </View>

        <View style={styles.kpiGrid}>
          <View style={styles.kpiCard}>
            <Text style={styles.kpiValue} testID="kpi-total-patients">
              {kpis?.total_patients ?? 0}
            </Text>
            <Text style={styles.kpiLabel}>Total Patients</Text>
          </View>

          <View style={styles.kpiCard}>
            <Text style={styles.kpiValue} testID="kpi-total-requisitions">
              {kpis?.total_requisitions ?? 0}
            </Text>
            <Text style={styles.kpiLabel}>Total Requisitions</Text>
          </View>

          <View style={styles.kpiCard}>
            <Text style={[styles.kpiValue, { color: colors.warning }]} testID="kpi-pending-requisitions">
              {kpis?.pending_requisitions ?? 0}
            </Text>
            <Text style={styles.kpiLabel}>Pending</Text>
          </View>

          <View style={styles.kpiCard}>
            <Text style={[styles.kpiValue, { color: colors.secondary }]} testID="kpi-approved-requisitions">
              {kpis?.approved_requisitions ?? 0}
            </Text>
            <Text style={styles.kpiLabel}>Approved</Text>
          </View>

          <View style={styles.kpiCard}>
            <Text style={[styles.kpiValue, { color: colors.success }]} testID="kpi-dispensed-requisitions">
              {kpis?.dispensed_requisitions ?? 0}
            </Text>
            <Text style={styles.kpiLabel}>Dispensed</Text>
          </View>
        </View>

        {/* Recent Requisitions Section */}
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Recent Blood Requisitions</Text>
        </View>

        {recentRequisitions.length > 0 ? (
          <View style={styles.requisitionsList}>
            {recentRequisitions.map((req: DashboardRequisition) => {
              const urgencyStyle = getUrgencyBadgeStyle(req.urgency_level);
              const statusStyle = getStatusBadgeStyle(req.status);
              return (
                <View key={req.id} style={styles.requisitionCard} testID="recent-requisition-item">
                  <View style={styles.reqTopRow}>
                    <Text style={styles.patientName}>{req.patient_name}</Text>
                    <View style={styles.bloodBadge}>
                      <Text style={styles.bloodBadgeText}>{req.blood_group}</Text>
                    </View>
                  </View>

                  <View style={styles.reqDetailRow}>
                    <Text style={styles.reqDetailText}>
                      <Text style={styles.boldText}>{req.units_needed}</Text> {req.units_needed === 1 ? "unit" : "units"} requested
                    </Text>

                    <View style={styles.badgesGroup}>
                      <View style={[styles.badgeContainer, urgencyStyle.container]}>
                        <Text style={[styles.badgeText, urgencyStyle.text]}>
                          {req.urgency_level.toUpperCase()}
                        </Text>
                      </View>

                      <View style={[styles.badgeContainer, statusStyle.container]}>
                        <Text style={[styles.badgeText, statusStyle.text]}>
                          {req.status.toUpperCase()}
                        </Text>
                      </View>
                    </View>
                  </View>

                  <View style={styles.reqFooterRow}>
                    <Text style={styles.dateText}>Requested: {req.created_at}</Text>
                  </View>
                </View>
              );
            })}
          </View>
        ) : (
          <View style={styles.emptyContainer} testID="empty-recent-requisitions">
            <Text style={styles.emptyTitle}>No Requisitions Found</Text>
            <Text style={styles.emptySubtitle}>There are no recent blood requisitions registered for this hospital facility.</Text>
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  scrollContainer: {
    flex: 1,
  },
  scrollContent: {
    padding: spacing.md,
    paddingBottom: spacing.xl,
  },
  headerContainer: {
    marginBottom: spacing.md,
    alignItems: "center",
  },
  appTitle: {
    fontSize: 22,
    fontWeight: "bold",
    color: colors.textPrimary,
    textAlign: "center",
  },
  subtitle: {
    fontSize: 14,
    color: colors.textSecondary,
    textAlign: "center",
    marginTop: 2,
  },
  errorBanner: {
    backgroundColor: "#FEE2E2",
    borderWidth: 1,
    borderColor: "#EF4444",
    borderRadius: 8,
    padding: spacing.sm,
    marginBottom: spacing.md,
    alignItems: "center",
  },
  errorText: {
    color: "#B91C1C",
    fontSize: 14,
    textAlign: "center",
    marginBottom: spacing.xs,
  },
  retryButton: {
    backgroundColor: colors.danger,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
    borderRadius: 6,
    marginTop: 4,
  },
  retryButtonText: {
    color: "#FFFFFF",
    fontSize: 13,
    fontWeight: "600",
  },
  loadingCard: {
    backgroundColor: colors.card,
    borderRadius: 12,
    padding: spacing.lg,
    marginBottom: spacing.md,
    alignItems: "center",
    justifyContent: "center",
  },
  loadingText: {
    marginTop: spacing.sm,
    color: colors.textSecondary,
    fontSize: 14,
  },
  card: {
    backgroundColor: colors.card,
    borderRadius: 12,
    padding: spacing.md,
    marginBottom: spacing.lg,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  cardHeaderRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: spacing.sm,
    paddingBottom: spacing.xs,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: "bold",
    color: colors.textPrimary,
  },
  infoGrid: {
    marginBottom: spacing.sm,
  },
  infoGroup: {
    marginBottom: spacing.sm,
  },
  infoLabel: {
    fontSize: 11,
    color: colors.textSecondary,
    textTransform: "uppercase",
    fontWeight: "600",
    marginBottom: 2,
  },
  infoValue: {
    fontSize: 15,
    color: colors.textPrimary,
    fontWeight: "500",
  },
  logoutButton: {
    height: 44,
    backgroundColor: colors.primary,
    borderRadius: 8,
    justifyContent: "center",
    alignItems: "center",
    marginTop: spacing.xs,
  },
  logoutButtonDisabled: {
    opacity: 0.6,
  },
  logoutButtonText: {
    color: "#FFFFFF",
    fontSize: 15,
    fontWeight: "bold",
  },
  sectionHeader: {
    marginBottom: spacing.sm,
  },
  sectionTitle: {
    fontSize: 17,
    fontWeight: "bold",
    color: colors.textPrimary,
  },
  kpiGrid: {
    flexDirection: "row",
    flexWrap: "wrap",
    justifyContent: "space-between",
    marginBottom: spacing.lg,
  },
  kpiCard: {
    width: "48%",
    backgroundColor: colors.card,
    borderRadius: 10,
    padding: spacing.md,
    marginBottom: spacing.sm,
    alignItems: "center",
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 3,
    elevation: 1,
  },
  kpiValue: {
    fontSize: 24,
    fontWeight: "bold",
    color: colors.textPrimary,
    marginBottom: 2,
  },
  kpiLabel: {
    fontSize: 12,
    color: colors.textSecondary,
    textAlign: "center",
    fontWeight: "500",
  },
  requisitionsList: {
    marginBottom: spacing.md,
  },
  requisitionCard: {
    backgroundColor: colors.card,
    borderRadius: 10,
    padding: spacing.md,
    marginBottom: spacing.sm,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 3,
    elevation: 1,
  },
  reqTopRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: spacing.xs,
  },
  patientName: {
    fontSize: 16,
    fontWeight: "bold",
    color: colors.textPrimary,
  },
  bloodBadge: {
    backgroundColor: colors.primaryLight,
    borderWidth: 1,
    borderColor: colors.primary,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 12,
  },
  bloodBadgeText: {
    color: colors.primaryDark,
    fontWeight: "bold",
    fontSize: 12,
  },
  reqDetailRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginVertical: spacing.xs,
  },
  reqDetailText: {
    fontSize: 14,
    color: colors.textSecondary,
  },
  boldText: {
    fontWeight: "bold",
    color: colors.textPrimary,
  },
  badgesGroup: {
    flexDirection: "row",
    gap: 6,
  },
  badgeContainer: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  badgeText: {
    fontSize: 10,
    fontWeight: "bold",
  },
  badgeSuccess: { backgroundColor: "#DCFCE7" },
  badgeTextSuccess: { color: "#15803D" },
  badgeWarning: { backgroundColor: "#FEF3C7" },
  badgeTextWarning: { color: "#B45309" },
  badgeDanger: { backgroundColor: "#FEE2E2" },
  badgeTextDanger: { color: "#B91C1C" },
  badgeInfo: { backgroundColor: "#DBEAFE" },
  badgeTextInfo: { color: "#1D4ED8" },
  reqFooterRow: {
    marginTop: spacing.xs,
    paddingTop: spacing.xs,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  dateText: {
    fontSize: 11,
    color: colors.textSecondary,
  },
  emptyContainer: {
    backgroundColor: colors.card,
    borderRadius: 10,
    padding: spacing.xl,
    alignItems: "center",
    justifyContent: "center",
    marginBottom: spacing.md,
  },
  emptyTitle: {
    fontSize: 16,
    fontWeight: "bold",
    color: colors.textPrimary,
    marginBottom: spacing.xs,
  },
  emptySubtitle: {
    fontSize: 13,
    color: colors.textSecondary,
    textAlign: "center",
  },
});
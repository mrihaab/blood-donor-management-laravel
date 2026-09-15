import React from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  ActivityIndicator,
} from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { colors, spacing } from "../theme";
import { useRequisitionDetailQuery } from "../api/useRequisitions";
import { RequisitionDetailScreenProps } from "../navigation/types";
import { parseApiError } from "../utils/apiErrors";
import { RequisitionStatus, RequisitionUrgency } from "../types/requisition";

export const RequisitionDetailScreen: React.FC<RequisitionDetailScreenProps> = ({
  route,
  navigation,
}) => {
  const { requisitionId } = route.params;

  const {
    data: requisition,
    isLoading,
    isError,
    error,
    refetch,
  } = useRequisitionDetailQuery(requisitionId);

  const errorMessage = isError ? parseApiError(error).message : null;

  const getStatusStyle = (status: RequisitionStatus) => {
    switch (status) {
      case "pending":
        return { container: styles.badgePending, text: styles.badgeTextPending };
      case "approved":
        return { container: styles.badgeApproved, text: styles.badgeTextApproved };
      case "dispensed":
        return { container: styles.badgeDispensed, text: styles.badgeTextDispensed };
      case "rejected":
        return { container: styles.badgeRejected, text: styles.badgeTextRejected };
      default:
        return { container: styles.badgeDefault, text: styles.badgeTextDefault };
    }
  };

  const getUrgencyStyle = (urgency: RequisitionUrgency) => {
    switch (urgency) {
      case "emergency":
        return { container: styles.urgencyEmergency, text: styles.urgencyTextEmergency };
      case "urgent":
        return { container: styles.urgencyUrgent, text: styles.urgencyTextUrgent };
      default:
        return { container: styles.urgencyRoutine, text: styles.urgencyTextRoutine };
    }
  };

  const formatDate = (dateStr: string | null) => {
    if (!dateStr) return "N/A";
    try {
      const d = new Date(dateStr);
      return d.toLocaleString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    } catch {
      return dateStr;
    }
  };

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container} edges={["top"]} testID="requisition-detail-loading">
        <View style={styles.topNav}>
          <TouchableOpacity
            onPress={() => navigation.goBack()}
            style={styles.navBackButton}
            testID="back-to-requisition-list-btn"
          >
            <Text style={styles.navBackText}>‹ Back</Text>
          </TouchableOpacity>
        </View>
        <View style={styles.centered}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading requisition details...</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (isError || !requisition) {
    return (
      <SafeAreaView style={styles.container} edges={["top"]} testID="requisition-detail-error">
        <View style={styles.topNav}>
          <TouchableOpacity
            onPress={() => navigation.goBack()}
            style={styles.navBackButton}
            testID="back-to-requisition-list-btn"
          >
            <Text style={styles.navBackText}>‹ Back</Text>
          </TouchableOpacity>
        </View>
        <View style={styles.centered}>
          <Text style={styles.errorTitle}>Requisition Not Found</Text>
          <Text style={styles.errorMessage}>
            {errorMessage || "The requested requisition could not be found or has been removed."}
          </Text>
          <TouchableOpacity style={styles.retryButton} onPress={() => refetch()} testID="retry-btn">
            <Text style={styles.retryButtonText}>Try Again</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  const statusStyle = getStatusStyle(requisition.status);
  const urgencyStyle = getUrgencyStyle(requisition.urgency_level);

  return (
    <SafeAreaView style={styles.container} edges={["top"]} testID="requisition-detail-container">
      {/* Top Header */}
      <View style={styles.topNav}>
        <TouchableOpacity
          onPress={() => navigation.goBack()}
          style={styles.navBackButton}
          accessibilityRole="button"
          accessibilityLabel="Back"
          testID="back-to-requisition-list-btn"
        >
          <Text style={styles.navBackText}>‹ Requisitions</Text>
        </TouchableOpacity>
        <Text style={styles.topNavTitle}>REQ #{requisition.id}</Text>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Status Card Header */}
        <View style={styles.headerCard}>
          <View style={styles.headerRow}>
            <View style={styles.headerLeft}>
              <Text style={styles.patientName} testID="requisition-detail-patient-name">
                {requisition.patient_name}
              </Text>
              {requisition.patient?.mrn ? (
                <Text style={styles.patientMrn}>MRN: {requisition.patient.mrn}</Text>
              ) : null}
            </View>

            <View style={[styles.badge, statusStyle.container]} testID="requisition-detail-status">
              <Text style={[styles.badgeText, statusStyle.text]}>
                {requisition.status.toUpperCase()}
              </Text>
            </View>
          </View>

          {requisition.patient_id ? (
            <TouchableOpacity
              style={styles.patientLinkButton}
              onPress={() =>
                navigation.navigate("PatientDetail", { patientId: requisition.patient_id! })
              }
              accessibilityRole="button"
              testID="view-patient-profile-btn"
            >
              <Text style={styles.patientLinkText}>View Patient Record ›</Text>
            </TouchableOpacity>
          ) : null}
        </View>

        {/* Request Specifications */}
        <View style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Request Specifications</Text>

          <View style={styles.gridRow}>
            <View style={styles.gridCol}>
              <Text style={styles.label}>Blood Group</Text>
              <View style={styles.bloodBadgeInline}>
                <Text style={styles.bloodBadgeInlineText}>{requisition.blood_group}</Text>
              </View>
            </View>

            <View style={styles.gridCol}>
              <Text style={styles.label}>Units Needed</Text>
              <Text style={styles.valueHighlight}>{requisition.units_needed} Units</Text>
            </View>

            <View style={styles.gridCol}>
              <Text style={styles.label}>Urgency</Text>
              <View style={[styles.urgencyBadgeInline, urgencyStyle.container]} testID="requisition-detail-urgency">
                <Text style={[styles.urgencyTextInline, urgencyStyle.text]}>
                  {requisition.urgency_level.toUpperCase()}
                </Text>
              </View>
            </View>
          </View>

          {requisition.required_by ? (
            <View style={styles.infoBlock}>
              <Text style={styles.label}>Required By Date</Text>
              <Text style={styles.valueText}>{formatDate(requisition.required_by)}</Text>
            </View>
          ) : null}

          {requisition.reason ? (
            <View style={styles.infoBlock}>
              <Text style={styles.label}>Clinical Reason / Notes</Text>
              <Text style={styles.notesText} testID="requisition-detail-reason">
                {requisition.reason}
              </Text>
            </View>
          ) : null}
        </View>

        {/* Patient Location & Family Attendant Info */}
        {(requisition.ward_name || requisition.room_number || requisition.bed_number || requisition.attendant_name || requisition.attendant_phone) ? (
          <View style={styles.sectionCard}>
            <Text style={styles.sectionTitle}>Location & Family Details</Text>

            {(requisition.ward_name || requisition.room_number || requisition.bed_number) ? (
              <View style={styles.infoBlock}>
                <Text style={styles.label}>Ward / Room / Bed</Text>
                <Text style={styles.valueText}>
                  {[requisition.ward_name, requisition.room_number ? `Room ${requisition.room_number}` : null, requisition.bed_number ? `Bed ${requisition.bed_number}` : null].filter(Boolean).join(" • ")}
                </Text>
              </View>
            ) : null}

            {(requisition.attendant_name || requisition.attendant_phone) ? (
              <View style={styles.infoBlock}>
                <Text style={styles.label}>Family Attendant</Text>
                <Text style={styles.valueText}>
                  {requisition.attendant_name || "N/A"} {requisition.attendant_phone ? `(${requisition.attendant_phone})` : ""}
                </Text>
              </View>
            ) : null}
          </View>
        ) : null}

        {/* Genuine Status Timeline */}
        <View style={styles.sectionCard} testID="requisition-detail-timeline">
          <Text style={styles.sectionTitle}>Status Timeline</Text>

          <View style={styles.timelineList}>
            <View style={styles.timelineItem}>
              <View style={[styles.timelineDot, styles.timelineDotActive]} />
              <View style={styles.timelineContent}>
                <Text style={styles.timelineTitle}>Requested</Text>
                <Text style={styles.timelineDate}>{formatDate(requisition.created_at)}</Text>
              </View>
            </View>

            {requisition.approved_at ? (
              <View style={styles.timelineItem}>
                <View style={[styles.timelineDot, styles.timelineDotActive]} />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineTitle}>Approved</Text>
                  <Text style={styles.timelineDate}>{formatDate(requisition.approved_at)}</Text>
                </View>
              </View>
            ) : null}

            {requisition.rejected_at ? (
              <View style={styles.timelineItem}>
                <View style={[styles.timelineDot, styles.timelineDotError]} />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineTitleError}>Rejected</Text>
                  <Text style={styles.timelineDate}>{formatDate(requisition.rejected_at)}</Text>
                </View>
              </View>
            ) : null}

            {requisition.status === "dispensed" ? (
              <View style={styles.timelineItem}>
                <View style={[styles.timelineDot, styles.timelineDotActive]} />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineTitle}>Blood Dispensed</Text>
                  <Text style={styles.timelineDate}>Dispensed from inventory</Text>
                </View>
              </View>
            ) : null}
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  topNav: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    backgroundColor: colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  navBackButton: {
    paddingVertical: spacing.xs,
  },
  navBackText: {
    fontSize: 16,
    color: colors.primary,
    fontWeight: "600",
  },
  topNavTitle: {
    fontSize: 18,
    fontWeight: "700",
    color: colors.text,
  },
  scrollContent: {
    padding: spacing.md,
  },
  headerCard: {
    backgroundColor: colors.surface,
    borderRadius: 12,
    padding: spacing.md,
    marginBottom: spacing.md,
    borderWidth: 1,
    borderColor: colors.border,
  },
  headerRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "flex-start",
  },
  headerLeft: {
    flex: 1,
    marginRight: spacing.sm,
  },
  patientName: {
    fontSize: 20,
    fontWeight: "700",
    color: colors.text,
  },
  patientMrn: {
    fontSize: 13,
    color: colors.textSecondary,
    marginTop: 2,
  },
  patientLinkButton: {
    marginTop: spacing.md,
    paddingTop: spacing.sm,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  patientLinkText: {
    fontSize: 14,
    color: colors.primary,
    fontWeight: "600",
  },
  sectionCard: {
    backgroundColor: colors.surface,
    borderRadius: 12,
    padding: spacing.md,
    marginBottom: spacing.md,
    borderWidth: 1,
    borderColor: colors.border,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: "700",
    color: colors.text,
    marginBottom: spacing.md,
  },
  gridRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },
  gridCol: {
    flex: 1,
    alignItems: "flex-start",
  },
  label: {
    fontSize: 12,
    color: colors.textSecondary,
    marginBottom: 4,
    fontWeight: "500",
  },
  valueHighlight: {
    fontSize: 15,
    fontWeight: "700",
    color: colors.text,
  },
  bloodBadgeInline: {
    backgroundColor: colors.primary + "15",
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
  },
  bloodBadgeInlineText: {
    color: colors.primary,
    fontWeight: "700",
    fontSize: 14,
  },
  infoBlock: {
    marginTop: spacing.md,
  },
  valueText: {
    fontSize: 14,
    color: colors.text,
    fontWeight: "500",
  },
  notesText: {
    fontSize: 14,
    color: colors.text,
    lineHeight: 20,
    backgroundColor: colors.background,
    padding: spacing.sm,
    borderRadius: 8,
  },
  badge: {
    paddingHorizontal: spacing.sm,
    paddingVertical: 4,
    borderRadius: 6,
  },
  badgeText: {
    fontSize: 12,
    fontWeight: "700",
  },
  badgePending: { backgroundColor: "#FEF3C7" },
  badgeTextPending: { color: "#D97706" },
  badgeApproved: { backgroundColor: "#DBEAFE" },
  badgeTextApproved: { color: "#2563EB" },
  badgeDispensed: { backgroundColor: "#D1FAE5" },
  badgeTextDispensed: { color: "#059669" },
  badgeRejected: { backgroundColor: "#FEE2E2" },
  badgeTextRejected: { color: "#DC2626" },
  badgeDefault: { backgroundColor: colors.border },
  badgeTextDefault: { color: colors.textSecondary },
  urgencyBadgeInline: {
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
  },
  urgencyTextInline: {
    fontSize: 11,
    fontWeight: "700",
  },
  urgencyEmergency: { backgroundColor: "#FEE2E2" },
  urgencyTextEmergency: { color: "#DC2626" },
  urgencyUrgent: { backgroundColor: "#FFEDD5" },
  urgencyTextUrgent: { color: "#C2410C" },
  urgencyRoutine: { backgroundColor: "#F3F4F6" },
  urgencyTextRoutine: { color: "#4B5563" },
  timelineList: {
    paddingLeft: spacing.xs,
  },
  timelineItem: {
    flexDirection: "row",
    alignItems: "flex-start",
    marginBottom: spacing.md,
  },
  timelineDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: colors.border,
    marginRight: spacing.md,
    marginTop: 4,
  },
  timelineDotActive: {
    backgroundColor: colors.primary,
  },
  timelineDotError: {
    backgroundColor: colors.error || "#DC2626",
  },
  timelineContent: {
    flex: 1,
  },
  timelineTitle: {
    fontSize: 14,
    fontWeight: "600",
    color: colors.text,
  },
  timelineTitleError: {
    fontSize: 14,
    fontWeight: "600",
    color: colors.error || "#DC2626",
  },
  timelineDate: {
    fontSize: 12,
    color: colors.textSecondary,
    marginTop: 2,
  },
  centered: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    padding: spacing.xl,
  },
  loadingText: {
    marginTop: spacing.sm,
    color: colors.textSecondary,
  },
  errorTitle: {
    fontSize: 18,
    fontWeight: "700",
    color: colors.error || "#DC2626",
    marginBottom: spacing.xs,
  },
  errorMessage: {
    fontSize: 14,
    color: colors.textSecondary,
    textAlign: "center",
    marginBottom: spacing.md,
  },
  retryButton: {
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.xs,
    borderRadius: 8,
  },
  retryButtonText: {
    color: colors.surface,
    fontWeight: "600",
  },
});

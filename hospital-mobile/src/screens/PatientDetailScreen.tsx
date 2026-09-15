import React from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  ActivityIndicator,
  RefreshControl,
} from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { colors, spacing } from "../theme";
import { usePatient } from "../api/usePatients";
import { PatientDetailScreenProps } from "../navigation/types";
import { parseApiError } from "../utils/apiErrors";
import { PatientRequisitionSummary } from "../types/patient";

export const PatientDetailScreen: React.FC<PatientDetailScreenProps> = ({ route, navigation }) => {
  const { patientId } = route.params || {};

  const {
    data: patient,
    isLoading,
    isError,
    error,
    refetch,
    isRefetching,
  } = usePatient(patientId);

  const errorMessage = isError ? parseApiError(error).message : null;

  const locationParts = [
    patient?.ward_name ? `Ward: ${patient.ward_name}` : null,
    patient?.room_number ? `Room: ${patient.room_number}` : null,
    patient?.bed_number ? `Bed: ${patient.bed_number}` : null,
  ].filter(Boolean);

  const locationText = locationParts.length > 0 ? locationParts.join(" • ") : "Not assigned";
  const requisitions = patient?.blood_requests || [];

  return (
    <SafeAreaView style={styles.container} testID="patient-detail-screen">
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
          accessibilityRole="button"
          accessibilityLabel="Back to patient list"
          testID="back-to-list-button"
        >
          <Text style={styles.backButtonText}>← Patients</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Patient Details</Text>
        {patient ? (
          <TouchableOpacity
            style={styles.editButton}
            onPress={() => navigation.navigate("PatientEdit", { patientId: patient.id })}
            accessibilityRole="button"
            accessibilityLabel="Edit patient details"
            testID="edit-patient-button"
          >
            <Text style={styles.editButtonText}>Edit</Text>
          </TouchableOpacity>
        ) : (
          <View style={{ width: 44 }} />
        )}
      </View>

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
        {/* Error Banner */}
        {errorMessage ? (
          <View style={styles.errorBanner} accessibilityRole="alert" testID="patient-detail-error-banner">
            <Text style={styles.errorText}>{errorMessage}</Text>
            <TouchableOpacity
              style={styles.retryButton}
              onPress={() => refetch()}
              accessibilityRole="button"
              accessibilityLabel="Retry loading patient"
              testID="patient-detail-retry-button"
            >
              <Text style={styles.retryButtonText}>Retry</Text>
            </TouchableOpacity>
          </View>
        ) : null}

        {/* Loading State */}
        {isLoading && !patient ? (
          <View style={styles.loadingCard} testID="patient-detail-loading">
            <ActivityIndicator size="large" color={colors.primary} />
            <Text style={styles.loadingText}>Loading patient record...</Text>
          </View>
        ) : null}

        {patient ? (
          <>
            {/* Identity & Status Card */}
            <View style={styles.card}>
              <View style={styles.identityRow}>
                <View style={{ flex: 1 }}>
                  <Text style={styles.patientName} testID="patient-detail-name">{patient.name}</Text>
                  <Text style={styles.mrnSubtext} testID="patient-detail-mrn">MRN: {patient.mrn}</Text>
                </View>
                <View style={[styles.badge, getStatusStyle(patient.status).container]} testID="patient-detail-status">
                  <Text style={[styles.badgeText, getStatusStyle(patient.status).text]}>
                    {patient.status.toUpperCase()}
                  </Text>
                </View>
              </View>
            </View>

            {/* Demographics Card */}
            <View style={styles.card}>
              <Text style={styles.cardTitle}>Demographics & Medical Info</Text>

              <View style={styles.infoGrid}>
                <View style={styles.infoRow}>
                  <Text style={styles.infoLabel}>Gender</Text>
                  <Text style={styles.infoValue} testID="patient-detail-gender">{patient.gender}</Text>
                </View>

                <View style={styles.infoRow}>
                  <Text style={styles.infoLabel}>Date of Birth</Text>
                  <Text style={styles.infoValue} testID="patient-detail-dob">{patient.date_of_birth}</Text>
                </View>

                <View style={styles.infoRow}>
                  <Text style={styles.infoLabel}>Blood Group</Text>
                  <Text style={styles.infoValueHighlight} testID="patient-detail-blood-group">
                    {patient.blood_group?.name || "Not recorded"}
                  </Text>
                </View>

                <View style={styles.infoRow}>
                  <Text style={styles.infoLabel}>Contact Number</Text>
                  <Text style={styles.infoValue} testID="patient-detail-contact">
                    {patient.contact_number || "Not provided"}
                  </Text>
                </View>

                <View style={styles.infoRow}>
                  <Text style={styles.infoLabel}>Hospital Location</Text>
                  <Text style={styles.infoValue} testID="patient-detail-location">{locationText}</Text>
                </View>
              </View>
            </View>

            {/* Requisitions Card */}
            <View style={styles.card}>
              <View style={{ flexDirection: "row", justifyContent: "space-between", alignItems: "center", marginBottom: spacing.sm }}>
                <Text style={styles.cardTitle}>Recent Blood Requisitions</Text>
                <TouchableOpacity
                  style={{ backgroundColor: colors.primary, paddingHorizontal: spacing.sm, paddingVertical: 4, borderRadius: 6 }}
                  onPress={() => navigation.navigate("RequisitionCreate", { patientId: patient.id })}
                  accessibilityRole="button"
                  accessibilityLabel="Create requisition for this patient"
                  testID="create-patient-requisition-btn"
                >
                  <Text style={{ color: colors.surface, fontWeight: "600", fontSize: 12 }}>+ Request Blood</Text>
                </TouchableOpacity>
              </View>

              {requisitions.length > 0 ? (
                <View style={styles.requisitionsList}>
                  {requisitions.map((req: PatientRequisitionSummary) => (
                    <TouchableOpacity
                      key={req.id}
                      style={styles.reqItem}
                      onPress={() => navigation.navigate("RequisitionDetail", { requisitionId: req.id })}
                      accessibilityRole="button"
                      accessibilityLabel={`View details for requisition #${req.id}`}
                      testID="patient-requisition-item"
                    >
                      <View style={styles.reqTopRow}>
                        <Text style={styles.reqBloodText}>{req.blood_group}</Text>
                        <Text style={styles.reqUnitsText}>{req.units_needed} {req.units_needed === 1 ? "unit" : "units"}</Text>
                      </View>
                      <View style={styles.reqBottomRow}>
                        <Text style={styles.reqUrgencyText}>Urgency: {req.urgency_level}</Text>
                        <Text style={styles.reqStatusText}>Status: {req.status}</Text>
                      </View>
                      {req.created_at ? (
                        <Text style={styles.reqDateText}>Requested: {req.created_at}</Text>
                      ) : null}
                    </TouchableOpacity>
                  ))}
                </View>
              ) : (
                <View style={styles.emptyReqContainer} testID="empty-patient-requisitions">
                  <Text style={styles.emptyReqTitle}>No Requisitions Found</Text>
                  <Text style={styles.emptyReqSubtitle}>
                    This patient has no recorded blood requisitions for this hospital facility.
                  </Text>
                </View>
              )}
            </View>
          </>
        ) : null}
      </ScrollView>
    </SafeAreaView>
  );
};

function getStatusStyle(status: string) {
  switch (status.toLowerCase()) {
    case "active":
      return { container: styles.badgeActive, text: styles.badgeTextActive };
    case "discharged":
      return { container: styles.badgeDischarged, text: styles.badgeTextDischarged };
    case "archived":
      return { container: styles.badgeArchived, text: styles.badgeTextArchived };
    default:
      return { container: styles.badgeDefault, text: styles.badgeTextDefault };
  }
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  header: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    backgroundColor: colors.card,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: "bold",
    color: colors.textPrimary,
  },
  backButton: {
    height: 44,
    minWidth: 44,
    justifyContent: "center",
  },
  backButtonText: {
    color: colors.primary,
    fontSize: 15,
    fontWeight: "bold",
  },
  editButton: {
    height: 44,
    minWidth: 44,
    paddingHorizontal: spacing.md,
    backgroundColor: colors.secondary,
    borderRadius: 6,
    justifyContent: "center",
    alignItems: "center",
  },
  editButtonText: {
    color: "#FFFFFF",
    fontSize: 14,
    fontWeight: "bold",
  },
  scrollContainer: {
    flex: 1,
  },
  scrollContent: {
    padding: spacing.md,
    paddingBottom: spacing.xl,
  },
  errorBanner: {
    backgroundColor: "#FEE2E2",
    borderWidth: 1,
    borderColor: "#EF4444",
    borderRadius: 8,
    marginBottom: spacing.md,
    padding: spacing.sm,
    alignItems: "center",
  },
  errorText: {
    color: "#B91C1C",
    fontSize: 14,
    textAlign: "center",
    marginBottom: spacing.xs,
  },
  retryButton: {
    height: 36,
    minWidth: 44,
    backgroundColor: colors.danger,
    paddingHorizontal: spacing.md,
    borderRadius: 6,
    justifyContent: "center",
    alignItems: "center",
  },
  retryButtonText: {
    color: "#FFFFFF",
    fontSize: 13,
    fontWeight: "bold",
  },
  loadingCard: {
    backgroundColor: colors.card,
    borderRadius: 12,
    padding: spacing.lg,
    alignItems: "center",
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
    marginBottom: spacing.md,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 3,
    elevation: 1,
  },
  identityRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },
  patientName: {
    fontSize: 20,
    fontWeight: "bold",
    color: colors.textPrimary,
  },
  mrnSubtext: {
    fontSize: 14,
    color: colors.textSecondary,
    marginTop: 2,
    fontWeight: "500",
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: "bold",
    color: colors.textPrimary,
    marginBottom: spacing.md,
    paddingBottom: spacing.xs,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  infoGrid: {},
  infoRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    paddingVertical: 2,
    marginBottom: spacing.sm,
  },
  infoLabel: {
    fontSize: 13,
    color: colors.textSecondary,
    fontWeight: "500",
  },
  infoValue: {
    fontSize: 14,
    color: colors.textPrimary,
    fontWeight: "600",
    textTransform: "capitalize",
  },
  infoValueHighlight: {
    fontSize: 14,
    color: colors.primaryDark,
    fontWeight: "bold",
  },
  badge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 6,
  },
  badgeText: {
    fontSize: 11,
    fontWeight: "bold",
  },
  badgeActive: { backgroundColor: "#DCFCE7" },
  badgeTextActive: { color: "#15803D" },
  badgeDischarged: { backgroundColor: "#FEF3C7" },
  badgeTextDischarged: { color: "#B45309" },
  badgeArchived: { backgroundColor: "#F1F5F9" },
  badgeTextArchived: { color: "#475569" },
  badgeDefault: { backgroundColor: "#DBEAFE" },
  badgeTextDefault: { color: "#1D4ED8" },
  requisitionsList: {},
  reqItem: {
    backgroundColor: colors.background,
    borderRadius: 8,
    padding: spacing.sm,
    borderWidth: 1,
    borderColor: colors.border,
    marginBottom: spacing.sm,
  },
  reqTopRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    marginBottom: 2,
  },
  reqBloodText: {
    fontSize: 14,
    fontWeight: "bold",
    color: colors.textPrimary,
  },
  reqUnitsText: {
    fontSize: 13,
    color: colors.textSecondary,
    fontWeight: "500",
  },
  reqBottomRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    marginTop: 2,
  },
  reqUrgencyText: {
    fontSize: 12,
    color: colors.textSecondary,
    textTransform: "capitalize",
  },
  reqStatusText: {
    fontSize: 12,
    color: colors.textSecondary,
    textTransform: "capitalize",
  },
  reqDateText: {
    fontSize: 11,
    color: colors.textSecondary,
    marginTop: 4,
  },
  emptyReqContainer: {
    padding: spacing.md,
    alignItems: "center",
  },
  emptyReqTitle: {
    fontSize: 14,
    fontWeight: "bold",
    color: colors.textPrimary,
    marginBottom: 2,
  },
  emptyReqSubtitle: {
    fontSize: 12,
    color: colors.textSecondary,
    textAlign: "center",
  },
});

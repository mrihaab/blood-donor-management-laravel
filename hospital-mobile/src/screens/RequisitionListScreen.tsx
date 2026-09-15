import React, { useState } from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  FlatList,
  ActivityIndicator,
  RefreshControl,
  ScrollView,
  TextInput,
} from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { colors, spacing } from "../theme";
import { useRequisitionsQuery } from "../api/useRequisitions";
import { RequisitionListItem, RequisitionStatus, RequisitionUrgency } from "../types/requisition";
import { RequisitionListScreenProps } from "../navigation/types";
import { parseApiError } from "../utils/apiErrors";

type FilterStatus = "all" | RequisitionStatus;

const STATUS_FILTERS: { label: string; value: FilterStatus }[] = [
  { label: "All", value: "all" },
  { label: "Pending", value: "pending" },
  { label: "Approved", value: "approved" },
  { label: "Dispensed", value: "dispensed" },
  { label: "Rejected", value: "rejected" },
];

export const RequisitionListScreen: React.FC<RequisitionListScreenProps> = ({ navigation }) => {
  const [statusFilter, setStatusFilter] = useState<FilterStatus>("all");
  const [searchText, setSearchText] = useState("");
  const [page, setPage] = useState(1);

  const handleStatusChange = (status: FilterStatus) => {
    setStatusFilter(status);
    setPage(1);
  };

  const handleSearchChange = (text: string) => {
    setSearchText(text);
    setPage(1);
  };

  const queryParams = {
    page,
    per_page: 15,
    status: statusFilter !== "all" ? statusFilter : undefined,
    search: searchText.trim() !== "" ? searchText.trim() : undefined,
  };

  const {
    data: requisitionResponse,
    isLoading,
    isError,
    error,
    refetch,
    isRefetching,
    isFetching,
  } = useRequisitionsQuery(undefined, queryParams);

  const requisitions = requisitionResponse?.data || [];
  const meta = requisitionResponse?.meta;
  const currentPage = meta?.current_page || page;
  const lastPage = meta?.last_page || 1;
  const total = meta?.total ?? 0;

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
    if (!dateStr) return null;
    try {
      const d = new Date(dateStr);
      return d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
    } catch {
      return dateStr;
    }
  };

  const renderItem = ({ item }: { item: RequisitionListItem }) => {
    const statusStyle = getStatusStyle(item.status);
    const urgencyStyle = getUrgencyStyle(item.urgency_level);

    return (
      <TouchableOpacity
        style={styles.card}
        onPress={() => navigation.navigate("RequisitionDetail", { requisitionId: item.id })}
        accessibilityRole="button"
        testID={`requisition-card-${item.id}`}
      >
        <View style={styles.cardHeader}>
          <View style={styles.patientInfoContainer}>
            <Text style={styles.patientNameText}>{item.patient_name}</Text>
            {item.patient?.mrn ? (
              <Text style={styles.mrnText}>MRN: {item.patient.mrn}</Text>
            ) : null}
          </View>
          <View style={[styles.badge, statusStyle.container]}>
            <Text style={[styles.badgeText, statusStyle.text]}>{item.status.toUpperCase()}</Text>
          </View>
        </View>

        <View style={styles.cardDetailsRow}>
          <View style={styles.bloodGroupChip}>
            <Text style={styles.bloodGroupText}>{item.blood_group}</Text>
          </View>

          <Text style={styles.unitsText}>{item.units_needed} Units</Text>

          <View style={[styles.urgencyBadge, urgencyStyle.container]}>
            <Text style={[styles.urgencyText, urgencyStyle.text]}>
              {item.urgency_level.toUpperCase()}
            </Text>
          </View>
        </View>

        <View style={styles.cardFooter}>
          <Text style={styles.dateText}>
            {item.created_at ? `Requested: ${formatDate(item.created_at)}` : "Recent Request"}
          </Text>
          <Text style={styles.chevronText}>Details ›</Text>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={styles.container} edges={["top"]} testID="requisition-list-container">
      {/* Top Header */}
      <View style={styles.topNav}>
        <Text style={styles.topNavTitle}>Blood Requisitions</Text>
        <TouchableOpacity
          style={styles.newButton}
          onPress={() => navigation.navigate("RequisitionCreate")}
          accessibilityRole="button"
          testID="create-requisition-nav-btn"
        >
          <Text style={styles.newButtonText}>+ New Request</Text>
        </TouchableOpacity>
      </View>

      {/* Filter Chips Scroll View */}
      <View style={styles.filterSection}>
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.filterContainer}
        >
          {STATUS_FILTERS.map((f) => {
            const isSelected = statusFilter === f.value;
            return (
              <TouchableOpacity
                key={f.value}
                style={[styles.chip, isSelected && styles.chipActive]}
                onPress={() => handleStatusChange(f.value)}
                accessibilityRole="button"
                testID={`status-filter-${f.value}`}
              >
                <Text style={[styles.chipText, isSelected && styles.chipTextActive]}>
                  {f.label}
                </Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>
      </View>

      {/* Search Input Bar */}
      <View style={styles.searchBarContainer}>
        <TextInput
          style={styles.searchInput}
          placeholder="Search by patient name or MRN..."
          placeholderTextColor={colors.textSecondary}
          value={searchText}
          onChangeText={handleSearchChange}
          testID="search-input"
        />
      </View>

      {/* List Content / Loading / Error / Empty States */}
      {isLoading ? (
        <View style={styles.centered} testID="requisition-list-loading">
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading requisitions...</Text>
        </View>
      ) : isError ? (
        <View style={styles.centered} testID="requisition-list-error">
          <Text style={styles.errorTitle}>Failed to Load Requisitions</Text>
          <Text style={styles.errorMessage}>{errorMessage || "An unexpected error occurred."}</Text>
          <TouchableOpacity style={styles.retryButton} onPress={() => refetch()} testID="retry-btn">
            <Text style={styles.retryButtonText}>Retry</Text>
          </TouchableOpacity>
        </View>
      ) : requisitions.length === 0 ? (
        <View style={styles.centered} testID="requisition-list-empty">
          <Text style={styles.emptyTitle}>No Requisitions Found</Text>
          <Text style={styles.emptyMessage}>
            {statusFilter !== "all" || searchText !== ""
              ? "No requisitions match the selected filters."
              : "There are currently no blood requisitions for your hospital."}
          </Text>
        </View>
      ) : (
        <FlatList
          data={requisitions}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.listContent}
          refreshControl={
            <RefreshControl
              refreshing={isRefetching}
              onRefresh={refetch}
              tintColor={colors.primary}
            />
          }
          ListFooterComponent={
            lastPage > 1 ? (
              <View style={styles.paginationContainer} testID="pagination-controls">
                <TouchableOpacity
                  style={[styles.pageButton, currentPage <= 1 && styles.pageButtonDisabled]}
                  disabled={currentPage <= 1 || isFetching}
                  onPress={() => setPage((p) => Math.max(p - 1, 1))}
                  testID="prev-page-btn"
                >
                  <Text style={styles.pageButtonText}>‹ Prev</Text>
                </TouchableOpacity>

                <Text style={styles.pageInfoText}>
                  Page {currentPage} of {lastPage} ({total} Total)
                </Text>

                <TouchableOpacity
                  style={[
                    styles.pageButton,
                    currentPage >= lastPage && styles.pageButtonDisabled,
                  ]}
                  disabled={currentPage >= lastPage || isFetching}
                  onPress={() => setPage((p) => Math.min(p + 1, lastPage))}
                  testID="next-page-btn"
                >
                  <Text style={styles.pageButtonText}>Next ›</Text>
                </TouchableOpacity>
              </View>
            ) : null
          }
        />
      )}
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
  topNavTitle: {
    fontSize: 20,
    fontWeight: "700",
    color: colors.text,
  },
  newButton: {
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
    borderRadius: 8,
  },
  newButtonText: {
    color: colors.surface,
    fontWeight: "600",
    fontSize: 14,
  },
  filterSection: {
    backgroundColor: colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
    paddingVertical: spacing.xs,
  },
  filterContainer: {
    paddingHorizontal: spacing.md,
  },
  chip: {
    paddingHorizontal: spacing.md,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: colors.background,
    marginRight: spacing.xs,
    borderWidth: 1,
    borderColor: colors.border,
  },
  chipActive: {
    backgroundColor: colors.primary,
    borderColor: colors.primary,
  },
  chipText: {
    fontSize: 13,
    color: colors.textSecondary,
    fontWeight: "500",
  },
  chipTextActive: {
    color: colors.surface,
    fontWeight: "600",
  },
  searchBarContainer: {
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
    backgroundColor: colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  searchInput: {
    height: 38,
    backgroundColor: colors.background,
    borderRadius: 8,
    paddingHorizontal: spacing.sm,
    fontSize: 14,
    color: colors.text,
    borderWidth: 1,
    borderColor: colors.border,
  },
  listContent: {
    padding: spacing.md,
  },
  card: {
    backgroundColor: colors.surface,
    borderRadius: 12,
    padding: spacing.md,
    marginBottom: spacing.sm,
    borderWidth: 1,
    borderColor: colors.border,
  },
  cardHeader: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "flex-start",
    marginBottom: spacing.xs,
  },
  patientInfoContainer: {
    flex: 1,
    marginRight: spacing.sm,
  },
  patientNameText: {
    fontSize: 16,
    fontWeight: "700",
    color: colors.text,
  },
  mrnText: {
    fontSize: 12,
    color: colors.textSecondary,
    marginTop: 2,
  },
  cardDetailsRow: {
    flexDirection: "row",
    alignItems: "center",
    marginVertical: spacing.xs,
  },
  bloodGroupChip: {
    backgroundColor: colors.primary + "15",
    paddingHorizontal: spacing.xs,
    paddingVertical: 2,
    borderRadius: 4,
    marginRight: spacing.sm,
  },
  bloodGroupText: {
    color: colors.primary,
    fontWeight: "700",
    fontSize: 14,
  },
  unitsText: {
    fontSize: 14,
    fontWeight: "600",
    color: colors.text,
    marginRight: spacing.sm,
  },
  cardFooter: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginTop: spacing.xs,
    paddingTop: spacing.xs,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  dateText: {
    fontSize: 12,
    color: colors.textSecondary,
  },
  chevronText: {
    fontSize: 12,
    color: colors.primary,
    fontWeight: "600",
  },
  badge: {
    paddingHorizontal: spacing.xs,
    paddingVertical: 2,
    borderRadius: 4,
  },
  badgeText: {
    fontSize: 11,
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
  urgencyBadge: {
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
  },
  urgencyText: {
    fontSize: 10,
    fontWeight: "700",
  },
  urgencyEmergency: { backgroundColor: "#FEE2E2" },
  urgencyTextEmergency: { color: "#DC2626" },
  urgencyUrgent: { backgroundColor: "#FFEDD5" },
  urgencyTextUrgent: { color: "#C2410C" },
  urgencyRoutine: { backgroundColor: "#F3F4F6" },
  urgencyTextRoutine: { color: "#4B5563" },
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
  emptyTitle: {
    fontSize: 18,
    fontWeight: "700",
    color: colors.text,
    marginBottom: spacing.xs,
  },
  emptyMessage: {
    fontSize: 14,
    color: colors.textSecondary,
    textAlign: "center",
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
  paginationContainer: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginTop: spacing.md,
    paddingTop: spacing.sm,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  pageButton: {
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
    backgroundColor: colors.surface,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: colors.border,
  },
  pageButtonDisabled: {
    opacity: 0.5,
  },
  pageButtonText: {
    fontSize: 13,
    fontWeight: "600",
    color: colors.primary,
  },
  pageInfoText: {
    fontSize: 12,
    color: colors.textSecondary,
  },
});

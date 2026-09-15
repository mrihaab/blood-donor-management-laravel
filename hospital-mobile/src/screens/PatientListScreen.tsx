import React, { useState, useEffect, useRef } from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  FlatList,
  ActivityIndicator,
  RefreshControl,
} from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { colors, spacing } from "../theme";
import { usePatients } from "../api/usePatients";
import { PatientListItem, PatientStatus } from "../types/patient";
import { PatientListScreenProps } from "../navigation/types";
import { parseApiError } from "../utils/apiErrors";

type FilterStatus = "all" | PatientStatus;

export const PatientListScreen: React.FC<PatientListScreenProps> = ({ navigation }) => {
  const [searchText, setSearchText] = useState("");
  const [debouncedSearch, setDebouncedSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState<FilterStatus>("all");
  const [page, setPage] = useState(1);

  const debounceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (debounceTimerRef.current) {
      clearTimeout(debounceTimerRef.current);
    }
    debounceTimerRef.current = setTimeout(() => {
      setDebouncedSearch(searchText.trim());
      setPage(1);
    }, 350);

    return () => {
      if (debounceTimerRef.current) {
        clearTimeout(debounceTimerRef.current);
      }
    };
  }, [searchText]);

  const handleStatusChange = (status: FilterStatus) => {
    setStatusFilter(status);
    setPage(1);
  };

  const handleClearSearch = () => {
    setSearchText("");
    setDebouncedSearch("");
    setPage(1);
  };

  const queryParams = {
    page,
    per_page: 15,
    search: debouncedSearch !== "" ? debouncedSearch : undefined,
    status: statusFilter !== "all" ? statusFilter : undefined,
  };

  const {
    data: patientResponse,
    isLoading,
    isError,
    error,
    refetch,
    isRefetching,
    isFetching,
  } = usePatients(undefined, queryParams);

  const patients = patientResponse?.data || [];
  const meta = patientResponse?.meta;
  const currentPage = meta?.current_page || page;
  const lastPage = meta?.last_page || 1;
  const total = meta?.total ?? 0;

  const errorMessage = isError ? parseApiError(error).message : null;

  const renderPatientCard = ({ item }: { item: PatientListItem }) => {
    const locationParts = [
      item.ward_name ? `Ward: ${item.ward_name}` : null,
      item.room_number ? `Room: ${item.room_number}` : null,
      item.bed_number ? `Bed: ${item.bed_number}` : null,
    ].filter(Boolean);

    return (
      <TouchableOpacity
        style={styles.card}
        onPress={() => navigation.navigate("PatientDetail", { patientId: item.id })}
        accessibilityRole="button"
        accessibilityLabel={`View details for ${item.name}`}
        testID="patient-card"
      >
        <View style={styles.cardHeader}>
          <Text style={styles.patientName} testID="patient-name">{item.name}</Text>
          <View style={[styles.badge, getStatusStyle(item.status).container]}>
            <Text style={[styles.badgeText, getStatusStyle(item.status).text]}>
              {item.status.toUpperCase()}
            </Text>
          </View>
        </View>

        <View style={styles.cardMetaRow}>
          <Text style={styles.mrnText} testID="patient-mrn">MRN: {item.mrn}</Text>
          <Text style={styles.genderText}>Gender: {item.gender}</Text>
        </View>

        <View style={styles.cardDetailRow}>
          <View style={styles.bloodBadge}>
            <Text style={styles.bloodBadgeText}>
              Blood Group: {item.blood_group?.name || "Not recorded"}
            </Text>
          </View>
          {locationParts.length > 0 ? (
            <Text style={styles.locationText}>{locationParts.join(" • ")}</Text>
          ) : (
            <Text style={styles.locationText}>Location: Unassigned</Text>
          )}
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={styles.container} testID="patient-list-screen">
      {/* Top Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.headerTitle}>Patients</Text>
          <Text style={styles.headerSubtitle}>Hospital Patient Directory</Text>
        </View>
        <TouchableOpacity
          style={styles.registerButton}
          onPress={() => navigation.navigate("PatientCreate")}
          accessibilityRole="button"
          accessibilityLabel="Register Patient"
          testID="register-patient-button"
        >
          <Text style={styles.registerButtonText}>+ Register Patient</Text>
        </TouchableOpacity>
      </View>

      {/* Search Input Bar */}
      <View style={styles.searchSection}>
        <View style={styles.searchInputContainer}>
          <TextInput
            style={styles.searchInput}
            placeholder="Search by name or MRN"
            placeholderTextColor={colors.textSecondary}
            value={searchText}
            onChangeText={setSearchText}
            accessibilityLabel="Search by name or MRN"
            testID="search-input"
          />
          {searchText !== "" ? (
            <TouchableOpacity
              style={styles.clearButton}
              onPress={handleClearSearch}
              accessibilityRole="button"
              accessibilityLabel="Clear search"
              testID="clear-search-button"
            >
              <Text style={styles.clearButtonText}>✕</Text>
            </TouchableOpacity>
          ) : null}
        </View>
      </View>

      {/* Status Filter Chips */}
      <View style={styles.filterSection}>
        {(["all", "active", "discharged", "archived"] as const).map((st) => (
          <TouchableOpacity
            key={st}
            style={[styles.filterChip, statusFilter === st && styles.filterChipActive]}
            onPress={() => handleStatusChange(st)}
            accessibilityRole="button"
            accessibilityLabel={`Filter by ${st}`}
            testID={`filter-${st}`}
          >
            <Text style={[styles.filterChipText, statusFilter === st && styles.filterChipTextActive]}>
              {st.charAt(0).toUpperCase() + st.slice(1)}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      {/* Error Banner */}
      {errorMessage ? (
        <View style={styles.errorBanner} accessibilityRole="alert" testID="patient-list-error-banner">
          <Text style={styles.errorText}>{errorMessage}</Text>
          <TouchableOpacity
            style={styles.retryButton}
            onPress={() => refetch()}
            accessibilityRole="button"
            accessibilityLabel="Retry loading patients"
            testID="patient-list-retry-button"
          >
            <Text style={styles.retryButtonText}>Retry</Text>
          </TouchableOpacity>
        </View>
      ) : null}

      {/* Initial Loading */}
      {isLoading && !patientResponse ? (
        <View style={styles.loadingContainer} testID="patient-list-loading">
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading patient directory...</Text>
        </View>
      ) : null}

      {/* Patient List */}
      <FlatList
        data={patients}
        renderItem={renderPatientCard}
        keyExtractor={(item) => String(item.id)}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl
            refreshing={isRefetching}
            onRefresh={refetch}
            colors={[colors.primary]}
            tintColor={colors.primary}
          />
        }
        ListEmptyComponent={
          !isLoading && !isError ? (
            <View style={styles.emptyContainer} testID={debouncedSearch || statusFilter !== "all" ? "no-search-results" : "empty-patient-list"}>
              <Text style={styles.emptyTitle}>
                {debouncedSearch || statusFilter !== "all" ? "No Matching Patients Found" : "No Patients Registered"}
              </Text>
              <Text style={styles.emptySubtitle}>
                {debouncedSearch || statusFilter !== "all"
                  ? "Try clearing filters or adjusting your search term."
                  : "No patient records exist for this hospital facility yet."}
              </Text>
            </View>
          ) : null
        }
      />

      {/* Pagination Footer */}
      {!isLoading && !isError && total > 0 ? (
        <View style={styles.paginationFooter} testID="pagination-footer">
          <TouchableOpacity
            style={[styles.pageButton, (currentPage <= 1 || isFetching) && styles.pageButtonDisabled]}
            onPress={() => setPage((p) => Math.max(1, p - 1))}
            disabled={currentPage <= 1 || isFetching}
            accessibilityRole="button"
            accessibilityLabel="Previous page"
            testID="prev-page-button"
          >
            <Text style={styles.pageButtonText}>Previous</Text>
          </TouchableOpacity>

          <Text style={styles.paginationText} testID="pagination-text">
            Page {currentPage} of {lastPage} ({total} total)
          </Text>

          <TouchableOpacity
            style={[styles.pageButton, (currentPage >= lastPage || isFetching) && styles.pageButtonDisabled]}
            onPress={() => setPage((p) => Math.min(lastPage, p + 1))}
            disabled={currentPage >= lastPage || isFetching}
            accessibilityRole="button"
            accessibilityLabel="Next page"
            testID="next-page-button"
          >
            <Text style={styles.pageButtonText}>Next</Text>
          </TouchableOpacity>
        </View>
      ) : null}
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
    fontSize: 20,
    fontWeight: "bold",
    color: colors.textPrimary,
  },
  headerSubtitle: {
    fontSize: 12,
    color: colors.textSecondary,
  },
  registerButton: {
    height: 44,
    minWidth: 44,
    paddingHorizontal: spacing.md,
    backgroundColor: colors.primary,
    borderRadius: 8,
    justifyContent: "center",
    alignItems: "center",
  },
  registerButtonText: {
    color: "#FFFFFF",
    fontSize: 14,
    fontWeight: "bold",
  },
  searchSection: {
    paddingHorizontal: spacing.md,
    paddingTop: spacing.sm,
    backgroundColor: colors.card,
  },
  searchInputContainer: {
    flexDirection: "row",
    alignItems: "center",
    backgroundColor: colors.background,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.border,
    paddingHorizontal: spacing.sm,
    height: 44,
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
    color: colors.textPrimary,
    paddingVertical: 0,
  },
  clearButton: {
    minWidth: 44,
    minHeight: 44,
    justifyContent: "center",
    alignItems: "center",
  },
  clearButtonText: {
    fontSize: 16,
    color: colors.textSecondary,
    fontWeight: "bold",
  },
  filterSection: {
    flexDirection: "row",
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    backgroundColor: colors.card,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  filterChip: {
    height: 36,
    minWidth: 44,
    paddingHorizontal: spacing.sm,
    marginRight: spacing.xs,
    borderRadius: 18,
    backgroundColor: colors.background,
    borderWidth: 1,
    borderColor: colors.border,
    justifyContent: "center",
    alignItems: "center",
  },
  filterChipActive: {
    backgroundColor: colors.primary,
    borderColor: colors.primary,
  },
  filterChipText: {
    fontSize: 13,
    color: colors.textSecondary,
    fontWeight: "500",
  },
  filterChipTextActive: {
    color: "#FFFFFF",
    fontWeight: "bold",
  },
  errorBanner: {
    backgroundColor: "#FEE2E2",
    borderWidth: 1,
    borderColor: "#EF4444",
    borderRadius: 8,
    margin: spacing.md,
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
  loadingContainer: {
    padding: spacing.xl,
    alignItems: "center",
  },
  loadingText: {
    marginTop: spacing.sm,
    color: colors.textSecondary,
    fontSize: 14,
  },
  listContent: {
    padding: spacing.md,
    paddingBottom: spacing.xl,
  },
  card: {
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
  cardHeader: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: spacing.xs,
  },
  patientName: {
    fontSize: 16,
    fontWeight: "bold",
    color: colors.textPrimary,
    flex: 1,
  },
  cardMetaRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    marginBottom: spacing.xs,
  },
  mrnText: {
    fontSize: 13,
    color: colors.textSecondary,
    fontWeight: "600",
  },
  genderText: {
    fontSize: 13,
    color: colors.textSecondary,
    textTransform: "capitalize",
  },
  cardDetailRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginTop: spacing.xs,
    paddingTop: spacing.xs,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  bloodBadge: {
    backgroundColor: colors.primaryLight,
    paddingHorizontal: spacing.xs,
    paddingVertical: 2,
    borderRadius: 4,
  },
  bloodBadgeText: {
    fontSize: 12,
    color: colors.primaryDark,
    fontWeight: "bold",
  },
  locationText: {
    fontSize: 12,
    color: colors.textSecondary,
  },
  badge: {
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
  },
  badgeText: {
    fontSize: 10,
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
  emptyContainer: {
    backgroundColor: colors.card,
    borderRadius: 10,
    padding: spacing.xl,
    alignItems: "center",
    marginTop: spacing.md,
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
  paginationFooter: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    backgroundColor: colors.card,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  pageButton: {
    height: 44,
    minWidth: 44,
    paddingHorizontal: spacing.md,
    backgroundColor: colors.primary,
    borderRadius: 6,
    justifyContent: "center",
    alignItems: "center",
  },
  pageButtonDisabled: {
    backgroundColor: colors.border,
    opacity: 0.5,
  },
  pageButtonText: {
    color: "#FFFFFF",
    fontSize: 13,
    fontWeight: "bold",
  },
  paginationText: {
    fontSize: 13,
    color: colors.textSecondary,
    fontWeight: "500",
  },
});

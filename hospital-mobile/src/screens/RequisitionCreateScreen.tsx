import React, { useState, useEffect, useRef, useMemo } from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  ScrollView,
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
} from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { colors, spacing } from "../theme";
import { usePatients } from "../api/usePatients";
import { useCreateRequisitionMutation } from "../api/useRequisitions";
import { RequisitionUrgency } from "../types/requisition";
import { PatientListItem } from "../types/patient";
import { RequisitionCreateScreenProps } from "../navigation/types";
import { parseApiError, FormErrors } from "../utils/apiErrors";

const CANONICAL_BLOOD_GROUPS = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];

const URGENCY_OPTIONS: { label: string; value: RequisitionUrgency }[] = [
  { label: "Routine", value: "routine" },
  { label: "Urgent", value: "urgent" },
  { label: "Emergency", value: "emergency" },
];

export const RequisitionCreateScreen: React.FC<RequisitionCreateScreenProps> = ({
  route,
  navigation,
}) => {
  const initialPatientId = route.params?.patientId;

  const { data: patientResponse, isLoading: isLoadingPatients } = usePatients(undefined, {
    status: "active",
    per_page: 50,
  });

  const activePatients = useMemo(() => patientResponse?.data || [], [patientResponse?.data]);

  const createRequisitionMutation = useCreateRequisitionMutation();

  const [selectedPatientId, setSelectedPatientId] = useState<number | null>(initialPatientId || null);
  const [bloodGroup, setBloodGroup] = useState<string>("");
  const [unitsNeeded, setUnitsNeeded] = useState<string>("1");
  const [urgencyLevel, setUrgencyLevel] = useState<RequisitionUrgency>("routine");
  const [reason, setReason] = useState<string>("");
  const [requiredBy, setRequiredBy] = useState<string>("");
  const [wardName, setWardName] = useState<string>("");
  const [roomNumber, setRoomNumber] = useState<string>("");
  const [bedNumber, setBedNumber] = useState<string>("");
  const [attendantName, setAttendantName] = useState<string>("");
  const [attendantPhone, setAttendantPhone] = useState<string>("");

  const [fieldErrors, setFieldErrors] = useState<FormErrors>({});
  const [formErrorMessage, setFormErrorMessage] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isDirty, setIsDirty] = useState(false);
  const [isPatientPickerOpen, setIsPatientPickerOpen] = useState(false);

  const isBypassingRef = useRef(false);

  const selectedPatient = useMemo(() => {
    return activePatients.find((p) => p.id === selectedPatientId);
  }, [activePatients, selectedPatientId]);

  const handlePatientSelect = (p: PatientListItem) => {
    setSelectedPatientId(p.id);
    if (p.blood_group?.name) {
      setBloodGroup(p.blood_group.name);
    }
    setIsPatientPickerOpen(false);
    setIsDirty(true);
    if (fieldErrors.patient_id) {
      setFieldErrors((prev) => ({ ...prev, patient_id: undefined }));
    }
  };

  const handleBloodGroupSelect = (bg: string) => {
    setBloodGroup(bg);
    setIsDirty(true);
    if (fieldErrors.blood_group) {
      setFieldErrors((prev) => ({ ...prev, blood_group: undefined }));
    }
  };

  const handleUrgencySelect = (urgency: RequisitionUrgency) => {
    setUrgencyLevel(urgency);
    setIsDirty(true);
    if (fieldErrors.urgency_level) {
      setFieldErrors((prev) => ({ ...prev, urgency_level: undefined }));
    }
  };

  const handleUnitsChange = (val: string) => {
    setUnitsNeeded(val);
    setIsDirty(true);
    if (fieldErrors.units_needed) {
      setFieldErrors((prev) => ({ ...prev, units_needed: undefined }));
    }
  };

  const handleReasonChange = (val: string) => {
    setReason(val);
    setIsDirty(true);
    if (fieldErrors.reason) {
      setFieldErrors((prev) => ({ ...prev, reason: undefined }));
    }
  };

  const handleRequiredByChange = (val: string) => {
    setRequiredBy(val);
    setIsDirty(true);
    if (fieldErrors.required_by) {
      setFieldErrors((prev) => ({ ...prev, required_by: undefined }));
    }
  };

  const handleWardNameChange = (val: string) => {
    setWardName(val);
    setIsDirty(true);
    if (fieldErrors.ward_name) {
      setFieldErrors((prev) => ({ ...prev, ward_name: undefined }));
    }
  };

  const handleRoomNumberChange = (val: string) => {
    setRoomNumber(val);
    setIsDirty(true);
    if (fieldErrors.room_number) {
      setFieldErrors((prev) => ({ ...prev, room_number: undefined }));
    }
  };

  const handleBedNumberChange = (val: string) => {
    setBedNumber(val);
    setIsDirty(true);
    if (fieldErrors.bed_number) {
      setFieldErrors((prev) => ({ ...prev, bed_number: undefined }));
    }
  };

  const handleAttendantNameChange = (val: string) => {
    setAttendantName(val);
    setIsDirty(true);
    if (fieldErrors.attendant_name) {
      setFieldErrors((prev) => ({ ...prev, attendant_name: undefined }));
    }
  };

  const handleAttendantPhoneChange = (val: string) => {
    setAttendantPhone(val);
    setIsDirty(true);
    if (fieldErrors.attendant_phone) {
      setFieldErrors((prev) => ({ ...prev, attendant_phone: undefined }));
    }
  };

  const isPending = isSubmitting || Boolean(createRequisitionMutation?.isPending);

  useEffect(() => {
    const unsubscribe = navigation.addListener("beforeRemove", (e) => {
      if (isBypassingRef.current || isPending || !isDirty) {
        return;
      }

      e.preventDefault();

      Alert.alert(
        "Discard Unsaved Request?",
        "You have unsaved changes in this blood requisition. Are you sure you want to leave?",
        [
          { text: "Stay", style: "cancel" },
          {
            text: "Discard",
            style: "destructive",
            onPress: () => {
              isBypassingRef.current = true;
              navigation.dispatch(e.data.action);
            },
          },
        ]
      );
    });

    return unsubscribe;
  }, [navigation, isPending, isDirty]);

  const handleSubmit = async () => {
    setFieldErrors({});
    setFormErrorMessage(null);

    const clientErrors: FormErrors = {};

    if (!selectedPatientId) {
      clientErrors.patient_id = "Please select a patient for this requisition.";
    }
    if (!bloodGroup || bloodGroup.trim() === "") {
      clientErrors.blood_group = "Please select a blood group.";
    }

    const parsedUnits = parseInt(unitsNeeded, 10);
    if (isNaN(parsedUnits) || parsedUnits < 1 || parsedUnits > 50) {
      clientErrors.units_needed = "Units needed must be an integer between 1 and 50.";
    }

    if (Object.keys(clientErrors).length > 0) {
      setFieldErrors(clientErrors);
      setFormErrorMessage("Please resolve the highlighted validation errors.");
      return;
    }

    setIsSubmitting(true);

    try {
      await createRequisitionMutation.mutateAsync({
        patient_id: selectedPatientId!,
        blood_group: bloodGroup,
        units_needed: parsedUnits,
        urgency_level: urgencyLevel,
        reason: reason.trim() !== "" ? reason.trim() : undefined,
        required_by: requiredBy.trim() !== "" ? requiredBy.trim() : undefined,
        ward_name: wardName.trim() !== "" ? wardName.trim() : undefined,
        room_number: roomNumber.trim() !== "" ? roomNumber.trim() : undefined,
        bed_number: bedNumber.trim() !== "" ? bedNumber.trim() : undefined,
        attendant_name: attendantName.trim() !== "" ? attendantName.trim() : undefined,
        attendant_phone: attendantPhone.trim() !== "" ? attendantPhone.trim() : undefined,
      });

      isBypassingRef.current = true;
      setIsDirty(false);

      Alert.alert(
        "Requisition Created",
        "The blood requisition has been created successfully.",
        [
          {
            text: "OK",
            onPress: () => {
              navigation.navigate("RequisitionList");
            },
          },
        ]
      );
    } catch (err) {
      const parsed = parseApiError(err);
      setFormErrorMessage(parsed.message);
      setFieldErrors(parsed.fieldErrors);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={["top"]} testID="requisition-form-container">
      {/* Top Header */}
      <View style={styles.topNav}>
        <TouchableOpacity
          onPress={() => navigation.goBack()}
          style={styles.navBackButton}
          accessibilityRole="button"
          accessibilityLabel="Back"
          testID="back-btn"
        >
          <Text style={styles.navBackText}>‹ Cancel</Text>
        </TouchableOpacity>
        <Text style={styles.topNavTitle}>New Blood Request</Text>
        <TouchableOpacity
          onPress={handleSubmit}
          disabled={isPending}
          style={[styles.saveNavButton, isPending && styles.saveNavButtonDisabled]}
          accessibilityRole="button"
          accessibilityLabel="Submit Requisition"
          testID="submit-requisition-btn"
        >
          {isPending ? (
            <ActivityIndicator size="small" color={colors.surface} />
          ) : (
            <Text style={styles.saveNavText}>Submit</Text>
          )}
        </TouchableOpacity>
      </View>

      <KeyboardAvoidingView
        style={{ flex: 1 }}
        behavior={Platform.OS === "ios" ? "padding" : undefined}
      >
        <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled">
          {formErrorMessage ? (
            <View style={styles.formErrorBox} testID="form-error-banner">
              <Text style={styles.formErrorText}>{formErrorMessage}</Text>
            </View>
          ) : null}

          {/* Patient Selection Card */}
          <View style={styles.card}>
            <Text style={styles.sectionTitle}>1. Select Patient</Text>

            {isLoadingPatients ? (
              <View style={styles.patientLoadingRow}>
                <ActivityIndicator size="small" color={colors.primary} />
                <Text style={styles.patientLoadingText}>Loading hospital patients...</Text>
              </View>
            ) : (
              <View>
                <TouchableOpacity
                  style={[styles.pickerTrigger, Boolean(fieldErrors.patient_id) && styles.inputError]}
                  onPress={() => setIsPatientPickerOpen(!isPatientPickerOpen)}
                  accessibilityRole="button"
                  testID="patient-picker"
                >
                  <Text
                    style={selectedPatient ? styles.pickerTriggerText : styles.pickerTriggerPlaceholder}
                  >
                    {selectedPatient
                      ? `${selectedPatient.name} (MRN: ${selectedPatient.mrn})`
                      : "Tap to select patient..."}
                  </Text>
                  <Text style={styles.pickerArrow}>{isPatientPickerOpen ? "▲" : "▼"}</Text>
                </TouchableOpacity>

                {fieldErrors.patient_id ? (
                  <Text style={styles.fieldErrorText} testID="field-error-patient_id">
                    {fieldErrors.patient_id}
                  </Text>
                ) : null}

                {/* Patient Dropdown List */}
                {isPatientPickerOpen ? (
                  <View style={styles.dropdownContainer}>
                    {activePatients.length === 0 ? (
                      <Text style={styles.dropdownEmptyText}>No active patients found.</Text>
                    ) : (
                      activePatients.map((p) => {
                        const isSelected = p.id === selectedPatientId;
                        return (
                          <TouchableOpacity
                            key={p.id}
                            style={[styles.dropdownItem, isSelected && styles.dropdownItemActive]}
                            onPress={() => handlePatientSelect(p)}
                            testID={`patient-option-${p.id}`}
                          >
                            <View>
                              <Text style={[styles.dropdownItemName, isSelected && styles.dropdownItemNameActive]}>
                                {p.name}
                              </Text>
                              <Text style={styles.dropdownItemMrn}>MRN: {p.mrn}</Text>
                            </View>
                            {isSelected ? <Text style={styles.checkMark}>✓</Text> : null}
                          </TouchableOpacity>
                        );
                      })
                    )}
                  </View>
                ) : null}
              </View>
            )}
          </View>

          {/* Blood & Units Specifications */}
          <View style={styles.card}>
            <Text style={styles.sectionTitle}>2. Requisition Specifications</Text>

            {/* Blood Group Selector */}
            <View style={styles.formGroup}>
              <Text style={styles.inputLabel}>
                Blood Group <Text style={styles.requiredStar}>*</Text>
              </Text>
              <View style={styles.chipGrid}>
                {CANONICAL_BLOOD_GROUPS.map((bg) => {
                  const isSelected = bloodGroup === bg;
                  return (
                    <TouchableOpacity
                      key={bg}
                      style={[styles.bloodGroupChip, isSelected && styles.bloodGroupChipActive]}
                      onPress={() => handleBloodGroupSelect(bg)}
                      accessibilityRole="button"
                      accessibilityState={{ selected: isSelected }}
                      testID={`blood-group-chip-${bg}`}
                    >
                      <Text style={[styles.bloodGroupChipText, isSelected && styles.bloodGroupChipTextActive]}>
                        {bg}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </View>
              {fieldErrors.blood_group ? (
                <Text style={styles.fieldErrorText} testID="field-error-blood_group">
                  {fieldErrors.blood_group}
                </Text>
              ) : null}
            </View>

            {/* Units Needed Input */}
            <View style={styles.formGroup}>
              <Text style={styles.inputLabel}>
                Units Needed (1 - 50) <Text style={styles.requiredStar}>*</Text>
              </Text>
              <TextInput
                style={[styles.textInput, Boolean(fieldErrors.units_needed) && styles.inputError]}
                value={unitsNeeded}
                onChangeText={handleUnitsChange}
                keyboardType="number-pad"
                maxLength={2}
                placeholder="1"
                testID="units-input"
              />
              {fieldErrors.units_needed ? (
                <Text style={styles.fieldErrorText} testID="field-error-units_needed">
                  {fieldErrors.units_needed}
                </Text>
              ) : null}
            </View>

            {/* Urgency Level Chips */}
            <View style={styles.formGroup}>
              <Text style={styles.inputLabel}>
                Urgency Level <Text style={styles.requiredStar}>*</Text>
              </Text>
              <View style={styles.urgencyRow}>
                {URGENCY_OPTIONS.map((u) => {
                  const isSelected = urgencyLevel === u.value;
                  return (
                    <TouchableOpacity
                      key={u.value}
                      style={[styles.urgencyChip, isSelected && styles.urgencyChipActive]}
                      onPress={() => handleUrgencySelect(u.value)}
                      accessibilityRole="button"
                      accessibilityState={{ selected: isSelected }}
                      testID={`urgency-chip-${u.value}`}
                    >
                      <Text style={[styles.urgencyChipText, isSelected && styles.urgencyChipTextActive]}>
                        {u.label}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </View>
              {fieldErrors.urgency_level ? (
                <Text style={styles.fieldErrorText} testID="field-error-urgency_level">
                  {fieldErrors.urgency_level}
                </Text>
              ) : null}
            </View>

            {/* Required By Date Input */}
            <View style={styles.formGroup}>
              <Text style={styles.inputLabel}>Required By Date (Optional)</Text>
              <TextInput
                style={[styles.textInput, Boolean(fieldErrors.required_by) && styles.inputError]}
                value={requiredBy}
                onChangeText={handleRequiredByChange}
                placeholder="YYYY-MM-DD HH:MM:SS"
                testID="required-by-input"
              />
              {fieldErrors.required_by ? (
                <Text style={styles.fieldErrorText} testID="field-error-required_by">
                  {fieldErrors.required_by}
                </Text>
              ) : null}
            </View>
          </View>

          {/* Clinical Reason & Details Card */}
          <View style={styles.card}>
            <Text style={styles.sectionTitle}>3. Clinical Reason & Location</Text>

            <View style={styles.formGroup}>
              <Text style={styles.inputLabel}>Reason / Clinical Indication</Text>
              <TextInput
                style={[styles.textAreaInput, Boolean(fieldErrors.reason) && styles.inputError]}
                value={reason}
                onChangeText={handleReasonChange}
                multiline
                numberOfLines={4}
                placeholder="Enter clinical reason or indication..."
                testID="reason-input"
              />
              {fieldErrors.reason ? (
                <Text style={styles.fieldErrorText} testID="field-error-reason">
                  {fieldErrors.reason}
                </Text>
              ) : null}
            </View>

            <View style={styles.formRow}>
              <View style={[styles.formGroup, { flex: 1, marginRight: spacing.xs }]}>
                <Text style={styles.inputLabel}>Ward Name</Text>
                <TextInput
                  style={styles.textInput}
                  value={wardName}
                  onChangeText={handleWardNameChange}
                  placeholder="e.g. ICU"
                  testID="ward-name-input"
                />
              </View>
              <View style={[styles.formGroup, { flex: 1, marginLeft: spacing.xs }]}>
                <Text style={styles.inputLabel}>Room</Text>
                <TextInput
                  style={styles.textInput}
                  value={roomNumber}
                  onChangeText={handleRoomNumberChange}
                  placeholder="e.g. 101"
                  testID="room-number-input"
                />
              </View>
              <View style={[styles.formGroup, { flex: 1, marginLeft: spacing.xs }]}>
                <Text style={styles.inputLabel}>Bed</Text>
                <TextInput
                  style={styles.textInput}
                  value={bedNumber}
                  onChangeText={handleBedNumberChange}
                  placeholder="e.g. B2"
                  testID="bed-number-input"
                />
              </View>
            </View>

            <View style={styles.formRow}>
              <View style={[styles.formGroup, { flex: 1, marginRight: spacing.xs }]}>
                <Text style={styles.inputLabel}>Attendant Name</Text>
                <TextInput
                  style={styles.textInput}
                  value={attendantName}
                  onChangeText={handleAttendantNameChange}
                  placeholder="e.g. John Doe"
                  testID="attendant-name-input"
                />
              </View>
              <View style={[styles.formGroup, { flex: 1, marginLeft: spacing.xs }]}>
                <Text style={styles.inputLabel}>Attendant Phone</Text>
                <TextInput
                  style={styles.textInput}
                  value={attendantPhone}
                  onChangeText={handleAttendantPhoneChange}
                  keyboardType="phone-pad"
                  placeholder="e.g. 555-0199"
                  testID="attendant-phone-input"
                />
              </View>
            </View>
          </View>

          <TouchableOpacity
            style={[styles.submitButtonLarge, isPending && styles.submitButtonLargeDisabled]}
            onPress={handleSubmit}
            disabled={isPending}
            accessibilityRole="button"
            testID="submit-requisition-bottom-btn"
          >
            {isPending ? (
              <ActivityIndicator color={colors.surface} />
            ) : (
              <Text style={styles.submitButtonLargeText}>Submit Blood Requisition</Text>
            )}
          </TouchableOpacity>
        </ScrollView>
      </KeyboardAvoidingView>
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
  saveNavButton: {
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
    borderRadius: 8,
  },
  saveNavButtonDisabled: {
    opacity: 0.5,
  },
  saveNavText: {
    color: colors.surface,
    fontWeight: "600",
    fontSize: 14,
  },
  scrollContent: {
    padding: spacing.md,
  },
  formErrorBox: {
    backgroundColor: colors.error + "15",
    borderWidth: 1,
    borderColor: colors.error,
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  formErrorText: {
    color: colors.error,
    fontSize: 14,
    fontWeight: "600",
  },
  card: {
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
  patientLoadingRow: {
    flexDirection: "row",
    alignItems: "center",
    paddingVertical: spacing.sm,
  },
  patientLoadingText: {
    marginLeft: spacing.sm,
    color: colors.textSecondary,
    fontSize: 14,
  },
  pickerTrigger: {
    height: 48,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 8,
    paddingHorizontal: spacing.md,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    backgroundColor: colors.background,
  },
  pickerTriggerText: {
    fontSize: 15,
    color: colors.text,
    fontWeight: "600",
  },
  pickerTriggerPlaceholder: {
    fontSize: 15,
    color: colors.textSecondary,
  },
  pickerArrow: {
    fontSize: 12,
    color: colors.textSecondary,
  },
  dropdownContainer: {
    marginTop: spacing.xs,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 8,
    backgroundColor: colors.surface,
    maxHeight: 200,
  },
  dropdownItem: {
    padding: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },
  dropdownItemActive: {
    backgroundColor: colors.primary + "10",
  },
  dropdownItemName: {
    fontSize: 15,
    fontWeight: "600",
    color: colors.text,
  },
  dropdownItemNameActive: {
    color: colors.primary,
  },
  dropdownItemMrn: {
    fontSize: 12,
    color: colors.textSecondary,
    marginTop: 2,
  },
  dropdownEmptyText: {
    padding: spacing.md,
    color: colors.textSecondary,
    textAlign: "center",
  },
  checkMark: {
    color: colors.primary,
    fontWeight: "700",
    fontSize: 16,
  },
  formGroup: {
    marginBottom: spacing.md,
  },
  formRow: {
    flexDirection: "row",
  },
  inputLabel: {
    fontSize: 14,
    fontWeight: "600",
    color: colors.text,
    marginBottom: spacing.xs,
  },
  requiredStar: {
    color: colors.error,
  },
  chipGrid: {
    flexDirection: "row",
    flexWrap: "wrap",
    marginHorizontal: -4,
  },
  bloodGroupChip: {
    width: "23%",
    margin: "1%",
    height: 40,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: colors.background,
  },
  bloodGroupChipActive: {
    backgroundColor: colors.primary,
    borderColor: colors.primary,
  },
  bloodGroupChipText: {
    fontSize: 14,
    fontWeight: "600",
    color: colors.text,
  },
  bloodGroupChipTextActive: {
    color: colors.surface,
  },
  textInput: {
    height: 48,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 8,
    paddingHorizontal: spacing.md,
    fontSize: 15,
    color: colors.text,
    backgroundColor: colors.background,
  },
  textAreaInput: {
    minHeight: 80,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 8,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    fontSize: 15,
    color: colors.text,
    backgroundColor: colors.background,
    textAlignVertical: "top",
  },
  inputError: {
    borderColor: colors.error,
  },
  fieldErrorText: {
    color: colors.error,
    fontSize: 12,
    marginTop: 4,
  },
  urgencyRow: {
    flexDirection: "row",
    justifyContent: "space-between",
  },
  urgencyChip: {
    flex: 1,
    height: 40,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: "center",
    justifyContent: "center",
    marginHorizontal: 4,
    backgroundColor: colors.background,
  },
  urgencyChipActive: {
    backgroundColor: colors.primary,
    borderColor: colors.primary,
  },
  urgencyChipText: {
    fontSize: 13,
    fontWeight: "600",
    color: colors.text,
  },
  urgencyChipTextActive: {
    color: colors.surface,
  },
  submitButtonLarge: {
    height: 52,
    backgroundColor: colors.primary,
    borderRadius: 12,
    alignItems: "center",
    justifyContent: "center",
    marginTop: spacing.sm,
    marginBottom: spacing.xl,
  },
  submitButtonLargeDisabled: {
    opacity: 0.5,
  },
  submitButtonLargeText: {
    color: colors.surface,
    fontSize: 16,
    fontWeight: "700",
  },
});

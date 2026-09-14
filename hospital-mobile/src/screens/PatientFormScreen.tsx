import React, { useState, useEffect, useRef } from "react";
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
import { usePatient, useBloodGroups, useCreatePatient, useUpdatePatient } from "../api/usePatients";
import { PatientGender } from "../types/patient";
import { PatientCreateScreenProps, PatientEditScreenProps } from "../navigation/types";
import { parseApiError, FormErrors } from "../utils/apiErrors";

type PatientFormScreenProps = PatientCreateScreenProps | PatientEditScreenProps;

export const PatientFormScreen: React.FC<PatientFormScreenProps> = ({ route, navigation }) => {
  const isEditMode = route.name === "PatientEdit";
  const patientId = isEditMode ? (route.params as { patientId: number }).patientId : undefined;

  const {
    data: existingPatient,
    isLoading: isLoadingDetail,
  } = usePatient(patientId) || {};

  const { data: bloodGroups = [], isLoading: isLoadingBloodGroups } = useBloodGroups() || {};

  const createPatientMutation = useCreatePatient();
  const updatePatientMutation = useUpdatePatient();

  const [name, setName] = useState("");
  const [mrn, setMrn] = useState("");
  const [gender, setGender] = useState<PatientGender | "">("");
  const [dateOfBirth, setDateOfBirth] = useState("");
  const [bloodGroupId, setBloodGroupId] = useState<number | null>(null);
  const [contactNumber, setContactNumber] = useState("");
  const [wardName, setWardName] = useState("");
  const [roomNumber, setRoomNumber] = useState("");
  const [bedNumber, setBedNumber] = useState("");

  const [fieldErrors, setFieldErrors] = useState<FormErrors>({});
  const [formErrorMessage, setFormErrorMessage] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isDirty, setIsDirty] = useState(false);

  const isInitialPopulated = useRef(false);
  const isBypassingRef = useRef(false);

  useEffect(() => {
    if (isEditMode && existingPatient && !isInitialPopulated.current) {
      setName(existingPatient.name || "");
      setMrn(existingPatient.mrn || "");
      setGender(existingPatient.gender || "");
      setDateOfBirth(existingPatient.date_of_birth || "");
      setBloodGroupId(existingPatient.blood_group?.id || null);
      setContactNumber(existingPatient.contact_number || "");
      setWardName(existingPatient.ward_name || "");
      setRoomNumber(existingPatient.room_number || "");
      setBedNumber(existingPatient.bed_number || "");

      isInitialPopulated.current = true;
      setIsDirty(false);
    }
  }, [isEditMode, existingPatient]);

  const isPending =
    isSubmitting ||
    Boolean(createPatientMutation?.isPending) ||
    Boolean(updatePatientMutation?.isPending);

  useEffect(() => {
    const unsubscribe = navigation.addListener("beforeRemove", (e) => {
      if (isBypassingRef.current) {
        return;
      }

      if (isPending) {
        e.preventDefault();
        return;
      }

      if (!isDirty) {
        return;
      }

      e.preventDefault();

      Alert.alert(
        "Discard Unsaved Changes?",
        "You have unsaved changes. Are you sure you want to leave?",
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
  }, [navigation, isDirty, isPending]);

  const markDirty = () => {
    if (!isDirty) {
      setIsDirty(true);
    }
  };

  const handleCancel = () => {
    navigation.goBack();
  };

  const validateForm = (): boolean => {
    const errors: FormErrors = {};
    let isValid = true;

    if (!name.trim()) {
      errors.name = "Patient full name is required.";
      isValid = false;
    }

    if (!isEditMode && !mrn.trim()) {
      errors.mrn = "MRN is required.";
      isValid = false;
    }

    if (!gender) {
      errors.gender = "Please select a gender.";
      isValid = false;
    }

    const dobTrimmed = dateOfBirth.trim();
    if (!dobTrimmed) {
      errors.date_of_birth = "Date of birth is required.";
      isValid = false;
    } else {
      const dobRegex = /^\d{4}-\d{2}-\d{2}$/;
      if (!dobRegex.test(dobTrimmed)) {
        errors.date_of_birth = "Date of birth must match YYYY-MM-DD format.";
        isValid = false;
      } else {
        const parts = dobTrimmed.split("-").map((p) => parseInt(p, 10));
        const year = parts[0];
        const month = parts[1] - 1;
        const day = parts[2];

        const dobDate = new Date(year, month, day);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (
          dobDate.getFullYear() !== year ||
          dobDate.getMonth() !== month ||
          dobDate.getDate() !== day
        ) {
          errors.date_of_birth = "Date of birth must be a valid calendar date.";
          isValid = false;
        } else if (dobDate >= today) {
          errors.date_of_birth = "Date of birth must be earlier than today.";
          isValid = false;
        }
      }
    }

    setFieldErrors(errors);
    return isValid;
  };

  const handleSubmit = async () => {
    if (isSubmitting) return;

    setFormErrorMessage(null);
    setFieldErrors({});

    if (!validateForm()) {
      return;
    }

    setIsSubmitting(true);

    try {
      if (isEditMode) {
        if (!patientId) {
          throw new Error("Invalid patient ID");
        }

        const updatePayload = {
          name: name.trim(),
          gender: gender as PatientGender,
          date_of_birth: dateOfBirth.trim(),
          blood_group_id: bloodGroupId,
          contact_number: contactNumber.trim() !== "" ? contactNumber.trim() : null,
          ward_name: wardName.trim() !== "" ? wardName.trim() : null,
          room_number: roomNumber.trim() !== "" ? roomNumber.trim() : null,
          bed_number: bedNumber.trim() !== "" ? bedNumber.trim() : null,
        };

        await updatePatientMutation.mutateAsync({
          patientId,
          payload: updatePayload,
        });

        isBypassingRef.current = true;
        setIsDirty(false);
        setIsSubmitting(false);
        navigation.goBack();
      } else {
        const createPayload = {
          name: name.trim(),
          mrn: mrn.trim(),
          gender: gender as PatientGender,
          date_of_birth: dateOfBirth.trim(),
          blood_group_id: bloodGroupId,
          contact_number: contactNumber.trim() !== "" ? contactNumber.trim() : null,
          ward_name: wardName.trim() !== "" ? wardName.trim() : null,
          room_number: roomNumber.trim() !== "" ? roomNumber.trim() : null,
          bed_number: bedNumber.trim() !== "" ? bedNumber.trim() : null,
        };

        const created = await createPatientMutation.mutateAsync(createPayload);

        isBypassingRef.current = true;
        setIsDirty(false);
        setIsSubmitting(false);
        navigation.replace("PatientDetail", { patientId: created.id });
      }
    } catch (err) {
      isBypassingRef.current = false;
      setIsSubmitting(false);
      const parsed = parseApiError(err);
      setFormErrorMessage(parsed.message);
      if (Object.keys(parsed.fieldErrors).length > 0) {
        setFieldErrors(parsed.fieldErrors);
      }
    }
  };

  return (
    <SafeAreaView style={styles.container} testID="patient-form-screen">
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.cancelButton}
          onPress={handleCancel}
          accessibilityRole="button"
          accessibilityLabel="Cancel"
          testID="cancel-button"
        >
          <Text style={styles.cancelButtonText}>Cancel</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>{isEditMode ? "Edit Patient" : "Register Patient"}</Text>
        <TouchableOpacity
          style={[styles.saveButton, isPending && styles.saveButtonDisabled]}
          onPress={handleSubmit}
          disabled={isPending}
          accessibilityRole="button"
          accessibilityLabel={isEditMode ? "Save changes" : "Create patient"}
          testID="submit-button"
        >
          {isPending ? (
            <ActivityIndicator color="#FFFFFF" size="small" testID="submit-loading" />
          ) : (
            <Text style={styles.saveButtonText}>{isEditMode ? "Save" : "Create"}</Text>
          )}
        </TouchableOpacity>
      </View>

      <KeyboardAvoidingView
        behavior={Platform.OS === "ios" ? "padding" : undefined}
        style={{ flex: 1 }}
      >
        <ScrollView contentContainerStyle={styles.scrollContent}>
          {/* Global Form Error Banner */}
          {formErrorMessage ? (
            <View style={styles.errorBanner} accessibilityRole="alert" testID="form-error-banner">
              <Text style={styles.errorText}>{formErrorMessage}</Text>
            </View>
          ) : null}

          {/* Loading Detail for Edit Mode */}
          {isEditMode && isLoadingDetail && !existingPatient ? (
            <View style={styles.loadingContainer} testID="edit-loading">
              <ActivityIndicator size="large" color={colors.primary} />
              <Text style={styles.loadingText}>Loading existing patient data...</Text>
            </View>
          ) : null}

          {/* Form Fields */}
          {(!isEditMode || existingPatient) ? (
            <View style={styles.formCard}>
              {/* MRN Field */}
              <View style={styles.fieldGroup}>
                <Text style={styles.fieldLabel}>
                  Medical Record Number (MRN) {!isEditMode && <Text style={styles.requiredStar}>*</Text>}
                </Text>
                {isEditMode ? (
                  <View style={styles.readOnlyContainer} testID="read-only-mrn">
                    <Text style={styles.readOnlyText}>{mrn}</Text>
                    <Text style={styles.readOnlyHint}>(MRN cannot be edited after registration)</Text>
                  </View>
                ) : (
                  <TextInput
                    style={[styles.textInput, fieldErrors.mrn ? styles.inputError : null]}
                    placeholder="e.g. MRN-2026-001"
                    placeholderTextColor={colors.textSecondary}
                    value={mrn}
                    onChangeText={(val) => {
                      setMrn(val);
                      markDirty();
                    }}
                    accessibilityLabel="Medical Record Number"
                    testID="input-mrn"
                  />
                )}
                {fieldErrors.mrn ? (
                  <Text style={styles.fieldErrorText} testID="error-mrn">{fieldErrors.mrn}</Text>
                ) : null}
              </View>

              {/* Full Name */}
              <View style={styles.fieldGroup}>
                <Text style={styles.fieldLabel}>
                  Full Name <Text style={styles.requiredStar}>*</Text>
                </Text>
                <TextInput
                  style={[styles.textInput, fieldErrors.name ? styles.inputError : null]}
                  placeholder="e.g. John Doe"
                  placeholderTextColor={colors.textSecondary}
                  value={name}
                  onChangeText={(val) => {
                    setName(val);
                    markDirty();
                  }}
                  accessibilityLabel="Full Name"
                  testID="input-name"
                />
                {fieldErrors.name ? (
                  <Text style={styles.fieldErrorText} testID="error-name">{fieldErrors.name}</Text>
                ) : null}
              </View>

              {/* Gender Selection */}
              <View style={styles.fieldGroup}>
                <Text style={styles.fieldLabel}>
                  Gender <Text style={styles.requiredStar}>*</Text>
                </Text>
                <View style={styles.chipsRow}>
                  {(["male", "female", "other"] as const).map((g) => (
                    <TouchableOpacity
                      key={g}
                      style={[styles.chip, gender === g && styles.chipSelected]}
                      onPress={() => {
                        setGender(g);
                        markDirty();
                      }}
                      accessibilityRole="button"
                      accessibilityLabel={`Gender ${g}`}
                      testID={`gender-${g}`}
                    >
                      <Text style={[styles.chipText, gender === g && styles.chipTextSelected]}>
                        {g.charAt(0).toUpperCase() + g.slice(1)}
                      </Text>
                    </TouchableOpacity>
                  ))}
                </View>
                {fieldErrors.gender ? (
                  <Text style={styles.fieldErrorText} testID="error-gender">{fieldErrors.gender}</Text>
                ) : null}
              </View>

              {/* Date of Birth */}
              <View style={styles.fieldGroup}>
                <Text style={styles.fieldLabel}>
                  Date of Birth (YYYY-MM-DD) <Text style={styles.requiredStar}>*</Text>
                </Text>
                <TextInput
                  style={[styles.textInput, fieldErrors.date_of_birth ? styles.inputError : null]}
                  placeholder="YYYY-MM-DD"
                  placeholderTextColor={colors.textSecondary}
                  value={dateOfBirth}
                  onChangeText={(val) => {
                    setDateOfBirth(val);
                    markDirty();
                  }}
                  keyboardType="numbers-and-punctuation"
                  accessibilityLabel="Date of Birth"
                  testID="input-dob"
                />
                {fieldErrors.date_of_birth ? (
                  <Text style={styles.fieldErrorText} testID="error-dob">{fieldErrors.date_of_birth}</Text>
                ) : null}
              </View>

              {/* Blood Group Selection */}
              <View style={styles.fieldGroup}>
                <Text style={styles.fieldLabel}>Blood Group</Text>
                {isLoadingBloodGroups ? (
                  <ActivityIndicator color={colors.primary} size="small" />
                ) : (
                  <View style={styles.chipsWrap}>
                    <TouchableOpacity
                      style={[styles.chip, bloodGroupId === null && styles.chipSelected]}
                      onPress={() => {
                        setBloodGroupId(null);
                        markDirty();
                      }}
                      accessibilityRole="button"
                      accessibilityLabel="Blood group unrecorded"
                      testID="blood-group-chip-none"
                    >
                      <Text style={[styles.chipText, bloodGroupId === null && styles.chipTextSelected]}>
                        None / Unknown
                      </Text>
                    </TouchableOpacity>

                    {bloodGroups.map((bg) => (
                      <TouchableOpacity
                        key={bg.id}
                        style={[styles.chip, bloodGroupId === bg.id && styles.chipSelected]}
                        onPress={() => {
                          setBloodGroupId(bg.id);
                          markDirty();
                        }}
                        accessibilityRole="button"
                        accessibilityLabel={`Blood group ${bg.name}`}
                        testID={`blood-group-chip-${bg.id}`}
                      >
                        <Text style={[styles.chipText, bloodGroupId === bg.id && styles.chipTextSelected]}>
                          {bg.name}
                        </Text>
                      </TouchableOpacity>
                    ))}
                  </View>
                )}
                {fieldErrors.blood_group_id ? (
                  <Text style={styles.fieldErrorText} testID="error-blood_group_id">{fieldErrors.blood_group_id}</Text>
                ) : null}
              </View>

              {/* Contact Number */}
              <View style={styles.fieldGroup}>
                <Text style={styles.fieldLabel}>Contact Phone Number</Text>
                <TextInput
                  style={[styles.textInput, fieldErrors.contact_number ? styles.inputError : null]}
                  placeholder="e.g. +1234567890"
                  placeholderTextColor={colors.textSecondary}
                  value={contactNumber}
                  onChangeText={(val) => {
                    setContactNumber(val);
                    markDirty();
                  }}
                  keyboardType="phone-pad"
                  accessibilityLabel="Contact Phone Number"
                  testID="input-contact"
                />
                {fieldErrors.contact_number ? (
                  <Text style={styles.fieldErrorText} testID="error-contact_number">{fieldErrors.contact_number}</Text>
                ) : null}
              </View>

              {/* Location Fields: Ward, Room, Bed */}
              <Text style={styles.subSectionTitle}>Hospital Location Assignments</Text>

              <View style={styles.fieldGroup}>
                <Text style={styles.fieldLabel}>Ward Name</Text>
                <TextInput
                  style={[styles.textInput, fieldErrors.ward_name ? styles.inputError : null]}
                  placeholder="e.g. ICU / Ward 3B"
                  placeholderTextColor={colors.textSecondary}
                  value={wardName}
                  onChangeText={(val) => {
                    setWardName(val);
                    markDirty();
                  }}
                  accessibilityLabel="Ward Name"
                  testID="input-ward"
                />
                {fieldErrors.ward_name ? (
                  <Text style={styles.fieldErrorText} testID="error-ward_name">{fieldErrors.ward_name}</Text>
                ) : null}
              </View>

              <View style={styles.rowTwoFields}>
                <View style={[styles.fieldGroup, { flex: 1, marginRight: spacing.xs }]}>
                  <Text style={styles.fieldLabel}>Room Number</Text>
                  <TextInput
                    style={[styles.textInput, fieldErrors.room_number ? styles.inputError : null]}
                    placeholder="e.g. 302"
                    placeholderTextColor={colors.textSecondary}
                    value={roomNumber}
                    onChangeText={(val) => {
                      setRoomNumber(val);
                      markDirty();
                    }}
                    accessibilityLabel="Room Number"
                    testID="input-room"
                  />
                  {fieldErrors.room_number ? (
                    <Text style={styles.fieldErrorText} testID="error-room_number">{fieldErrors.room_number}</Text>
                  ) : null}
                </View>

                <View style={[styles.fieldGroup, { flex: 1, marginLeft: spacing.xs }]}>
                  <Text style={styles.fieldLabel}>Bed Number</Text>
                  <TextInput
                    style={[styles.textInput, fieldErrors.bed_number ? styles.inputError : null]}
                    placeholder="e.g. B-12"
                    placeholderTextColor={colors.textSecondary}
                    value={bedNumber}
                    onChangeText={(val) => {
                      setBedNumber(val);
                      markDirty();
                    }}
                    accessibilityLabel="Bed Number"
                    testID="input-bed"
                  />
                  {fieldErrors.bed_number ? (
                    <Text style={styles.fieldErrorText} testID="error-bed_number">{fieldErrors.bed_number}</Text>
                  ) : null}
                </View>
              </View>
            </View>
          ) : null}
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
  cancelButton: {
    height: 44,
    minWidth: 44,
    justifyContent: "center",
  },
  cancelButtonText: {
    color: colors.textSecondary,
    fontSize: 15,
  },
  saveButton: {
    height: 44,
    minWidth: 44,
    paddingHorizontal: spacing.md,
    backgroundColor: colors.primary,
    borderRadius: 6,
    justifyContent: "center",
    alignItems: "center",
  },
  saveButtonDisabled: {
    opacity: 0.6,
  },
  saveButtonText: {
    color: "#FFFFFF",
    fontSize: 14,
    fontWeight: "bold",
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
  },
  errorText: {
    color: "#B91C1C",
    fontSize: 14,
    textAlign: "center",
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
  formCard: {
    backgroundColor: colors.card,
    borderRadius: 12,
    padding: spacing.md,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 3,
    elevation: 1,
  },
  fieldGroup: {
    marginBottom: spacing.md,
  },
  fieldLabel: {
    fontSize: 13,
    fontWeight: "600",
    color: colors.textPrimary,
    marginBottom: spacing.xs,
  },
  requiredStar: {
    color: colors.danger,
  },
  textInput: {
    height: 44,
    backgroundColor: colors.background,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.border,
    paddingHorizontal: spacing.sm,
    fontSize: 14,
    color: colors.textPrimary,
  },
  inputError: {
    borderColor: colors.danger,
    backgroundColor: "#FEF2F2",
  },
  fieldErrorText: {
    fontSize: 12,
    color: colors.danger,
    marginTop: 2,
  },
  readOnlyContainer: {
    backgroundColor: colors.background,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.border,
    padding: spacing.sm,
  },
  readOnlyText: {
    fontSize: 15,
    fontWeight: "bold",
    color: colors.textPrimary,
  },
  readOnlyHint: {
    fontSize: 11,
    color: colors.textSecondary,
    marginTop: 2,
  },
  chipsRow: {
    flexDirection: "row",
    gap: spacing.xs,
  },
  chipsWrap: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: spacing.xs,
  },
  chip: {
    height: 44,
    minWidth: 44,
    paddingHorizontal: spacing.md,
    borderRadius: 8,
    backgroundColor: colors.background,
    borderWidth: 1,
    borderColor: colors.border,
    justifyContent: "center",
    alignItems: "center",
  },
  chipSelected: {
    backgroundColor: colors.primary,
    borderColor: colors.primary,
  },
  chipText: {
    fontSize: 14,
    color: colors.textSecondary,
    fontWeight: "500",
  },
  chipTextSelected: {
    color: "#FFFFFF",
    fontWeight: "bold",
  },
  subSectionTitle: {
    fontSize: 15,
    fontWeight: "bold",
    color: colors.textPrimary,
    marginTop: spacing.sm,
    marginBottom: spacing.sm,
    paddingTop: spacing.sm,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  rowTwoFields: {
    flexDirection: "row",
  },
});

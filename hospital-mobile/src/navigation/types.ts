import type { NativeStackScreenProps } from "@react-navigation/native-stack";

export type AuthenticatedStackParamList = {
  Dashboard: undefined;
  PatientList: undefined;
  PatientDetail: { patientId: number };
  PatientCreate: undefined;
  PatientEdit: { patientId: number };
  RequisitionList: undefined;
  RequisitionDetail: { requisitionId: number };
  RequisitionCreate: { patientId?: number } | undefined;
};

export type RootStackParamList = {
  Splash: undefined;
  Login: undefined;
  Dashboard: undefined;
  PatientList: undefined;
  PatientDetail: { patientId: number };
  PatientCreate: undefined;
  PatientEdit: { patientId: number };
  RequisitionList: undefined;
  RequisitionDetail: { requisitionId: number };
  RequisitionCreate: { patientId?: number } | undefined;
};

export type DashboardScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "Dashboard">;
export type PatientListScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "PatientList">;
export type PatientDetailScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "PatientDetail">;
export type PatientCreateScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "PatientCreate">;
export type PatientEditScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "PatientEdit">;
export type RequisitionListScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "RequisitionList">;
export type RequisitionDetailScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "RequisitionDetail">;
export type RequisitionCreateScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "RequisitionCreate">;

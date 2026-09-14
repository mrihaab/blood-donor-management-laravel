import type { NativeStackScreenProps } from "@react-navigation/native-stack";

export type AuthenticatedStackParamList = {
  Dashboard: undefined;
  PatientList: undefined;
  PatientDetail: { patientId: number };
  PatientCreate: undefined;
  PatientEdit: { patientId: number };
};

export type RootStackParamList = {
  Splash: undefined;
  Login: undefined;
  Dashboard: undefined;
  PatientList: undefined;
  PatientDetail: { patientId: number };
  PatientCreate: undefined;
  PatientEdit: { patientId: number };
};

export type DashboardScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "Dashboard">;
export type PatientListScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "PatientList">;
export type PatientDetailScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "PatientDetail">;
export type PatientCreateScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "PatientCreate">;
export type PatientEditScreenProps = NativeStackScreenProps<AuthenticatedStackParamList, "PatientEdit">;

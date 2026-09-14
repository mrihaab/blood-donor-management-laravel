import { authenticatedApiClient } from "./client";

export interface DashboardHospital {
  id: number;
  name: string;
  license_number: string;
  city: string | null;
  status: string;
}

export interface DashboardKpis {
  total_patients: number;
  total_requisitions: number;
  pending_requisitions: number;
  approved_requisitions: number;
  dispensed_requisitions: number;
}

export interface DashboardRequisition {
  id: number;
  patient_name: string;
  blood_group: string;
  units_needed: number;
  urgency_level: string;
  status: string;
  created_at: string;
}

export interface DashboardData {
  hospital: DashboardHospital;
  kpis: DashboardKpis;
  recent_requisitions: DashboardRequisition[];
}

export interface DashboardResponse {
  data: DashboardData;
}

export async function getDashboardApi(): Promise<DashboardData> {
  const response = await authenticatedApiClient.get<DashboardResponse>("/dashboard");
  return response.data.data;
}

export interface FormErrors {
  [key: string]: string | undefined;
}

export function parseApiError(error: unknown): { message: string; fieldErrors: FormErrors } {
  const fieldErrors: FormErrors = {};
  let message = "An error occurred. Please try again.";

  if (error && typeof error === "object" && "isAxiosError" in error) {
    const axiosErr = error as { response?: { status?: number; data?: { message?: string; errors?: Record<string, string[]> } } };
    const status = axiosErr.response?.status;
    const resData = axiosErr.response?.data;

    if (status === 422 && resData?.errors) {
      message = resData.message || "Please correct the errors in the form.";
      Object.keys(resData.errors).forEach((field) => {
        const errList = resData.errors![field];
        if (Array.isArray(errList) && errList.length > 0) {
          fieldErrors[field] = errList[0];
        }
      });
      return { message, fieldErrors };
    }

    if (status === 404) {
      return { message: "The requested resource was not found.", fieldErrors };
    }

    if (status === 403) {
      return { message: "You do not have permission to perform this action.", fieldErrors };
    }

    if (status === 401) {
      return { message: "Your session has expired. Please log in again.", fieldErrors };
    }

    if (status === 429) {
      return { message: "Too many requests. Please try again later.", fieldErrors };
    }

    if (status && status >= 500) {
      return { message: "Server error. Please try again later.", fieldErrors };
    }

    if (resData?.message) {
      return { message: resData.message, fieldErrors };
    }
  } else if (error instanceof TypeError || error instanceof Error) {
    message = error.message;
  }

  return { message, fieldErrors };
}

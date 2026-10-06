const API_BASE_URL = "http://localhost:8000/api";

export async function request(path, { method = "GET", body, token } = {}) {
  const headers = { Accept: "application/json" };
  if (body !== undefined) headers["Content-Type"] = "application/json";
  if (token) headers.Authorization = `Bearer ${token}`;

  const response = await fetch(`${API_BASE_URL}${path}`, {
    method,
    headers,
    body: body === undefined ? undefined : JSON.stringify(body),
  });

  let data;
  try {
    data = await response.json();
  } catch {
    throw new Error("API ส่งข้อมูลกลับมาในรูปแบบที่ไม่ถูกต้อง");
  }

  if (!response.ok) {
    const error = new Error(data.error || `คำขอไม่สำเร็จ (${response.status})`);
    error.status = response.status;
    throw error;
  }
  return data;
}

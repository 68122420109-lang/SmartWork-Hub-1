import { request } from "./api.js";

const form = document.querySelector("#login-form");
const message = document.querySelector("#message");
const dashboard = document.querySelector("#dashboard");
const summary = document.querySelector("#user-summary");
const logoutButton = document.querySelector("#logout-button");

function showDashboard(user) {
  form.hidden = true;
  dashboard.hidden = false;
  summary.textContent = `${user.email} · ${user.role}`;
}

function showLogin() {
  dashboard.hidden = true;
  form.hidden = false;
  form.reset();
}

const savedToken = localStorage.getItem("smartwork_token");
if (savedToken) {
  try {
    const { user } = await request("/me", { token: savedToken });
    showDashboard(user);
  } catch (error) {
    if (error.status === 401) {
      localStorage.removeItem("smartwork_token");
    } else {
      message.textContent = error.message;
    }
  }
}

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  message.textContent = "";
  const submitButton = form.querySelector('button[type="submit"]');
  submitButton.disabled = true;

  try {
    const formData = new FormData(form);
    const result = await request("/login", {
      method: "POST",
      body: {
        email: formData.get("email"),
        password: formData.get("password"),
      },
    });
    localStorage.setItem("smartwork_token", result.token);
    showDashboard(result.user);
  } catch (error) {
    message.textContent = error.message;
  } finally {
    submitButton.disabled = false;
  }
});

logoutButton.addEventListener("click", async () => {
  const token = localStorage.getItem("smartwork_token");
  try {
    if (token) await request("/logout", { method: "POST", token });
  } catch (error) {
    message.textContent = error.message;
    return;
  }

  localStorage.removeItem("smartwork_token");
  message.textContent = "";
  showLogin();
});

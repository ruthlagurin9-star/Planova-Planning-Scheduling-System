import { createContext, useContext, useState } from "react";
import api from "../services/api";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  // Restore the user when the app opens
  async function loadUser() {
    const token = localStorage.getItem("planova_token");

    if (!token) {
      setLoading(false);
      return;
    }

    try {
      const response = await api.get("/user");
      setUser(response.data);
    } catch {
      localStorage.removeItem("planova_token");
      setUser(null);
    } finally {
      setLoading(false);
    }
  }

  async function login(email, password) {
    const response = await api.post("/login", { email, password });

    localStorage.setItem("planova_token", response.data.token);
    setUser(response.data.user);

    return response.data.user;
  }

  async function register(name, email, password, password_confirmation) {
    const response = await api.post("/register", {
      name,
      email,
      password,
      password_confirmation,
    });

    localStorage.setItem("planova_token", response.data.token);
    setUser(response.data.user);

    return response.data.user;
  }

  async function logout() {
    try {
      await api.post("/logout");
    } finally {
      localStorage.removeItem("planova_token");
      setUser(null);
    }
  }

  return (
    <AuthContext.Provider
      value={{ user, setUser, loading, loadUser, login, register, logout }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}

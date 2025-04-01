import { defineStore } from "pinia";
import { jwtDecode } from "jwt-decode";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    jwt: localStorage.getItem("jwt") || "",
    role: null,
    id: null,  // ✅ Added id to state
  }),

  getters: {
    isFreelancer: (state) => state.role === "freelancer",
    isClientOrAdmin: (state) => state.role === "client",
    userId: (state) => state.id,
  },

  actions: {
    setToken(token) {
      this.jwt = token;
      localStorage.setItem("jwt", token);
      this.decodeToken();
    },

    decodeToken() {
      if (this.jwt) {
        try {
          const decoded = jwtDecode(this.jwt); 
          this.role = decoded.role;
          this.id = decoded.id;
        } catch (error) {
          console.error("Invalid token:", error);
          this.jwt = "";
          this.role = null;
          this.id = null;
        }
      }
    },

    logout() {
      this.jwt = "";
      this.role = null;
      this.id = null;
      localStorage.removeItem("jwt");
    }
  }
});

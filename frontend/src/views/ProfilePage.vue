<template>
    <div class="profile-container">
      <h1>Manage Your Profile</h1>
  
      <form @submit.prevent="updateProfile">
        <div class="form-group">
          <label for="name">Name:</label>
          <input v-model="user.name" id="name" type="text" required />
        </div>
  
        <div class="form-group">
          <label for="email">Email:</label>
          <input v-model="user.email" id="email" type="email" required />
        </div>
  
        <div class="form-group">
          <label for="password">New Password (optional):</label>
          <input v-model="user.password" id="password" type="password" />
        </div>
  
        <button type="submit">Update Profile</button>
      </form>
    </div>
  </template>
  
  <script>
  import api from "@/api/api";
  import { useAuthStore } from "@/stores/authStore";
  
  export default {
    name: "ProfilePage",
    setup() {
      const authStore = useAuthStore();
      return { authStore };
    },
    data() {
      return {
        user: {
          name: "",
          email: "",
          password: "",
        },
      };
    },
    methods: {
      async fetchUser() {
        try {
          const token = localStorage.getItem("jwt");
          const response = await api.get(`/api/users/${this.authStore.id}`, {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          });
          this.user.name = response.data.name;
          this.user.email = response.data.email;
        } catch (error) {
          alert("❌ Failed to fetch user info");
          console.error(error);
        }
      },
  
      async updateProfile() {
        try {
          const token = localStorage.getItem("jwt");
          const payload = {
            name: this.user.name,
            email: this.user.email,
          };
          if (this.user.password) {
            payload.password = this.user.password;
          }
  
          await api.put("/api/users/update", payload, {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          });
  
          alert("✅ Profile updated successfully");
          this.user.password = "";
        } catch (error) {
          alert("❌ Failed to update profile: " + (error.response?.data?.message || "Unknown error"));
          console.error(error);
        }
      },
    },
    mounted() {
      this.fetchUser();
    },
  };
  </script>
  
  <style scoped>
  .profile-container {
    max-width: 600px;
    margin: 30px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  h1 {
    text-align: center;
    color: #222831;
    margin-bottom: 20px;
  }
  
  .form-group {
    margin-bottom: 15px;
  }
  
  label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
    color: #222;
  }
  
  input {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  
  button {
    width: 100%;
    padding: 12px;
    background: #00adb5;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  
  button:hover {
    background: #008b8b;
  }
  </style>
  
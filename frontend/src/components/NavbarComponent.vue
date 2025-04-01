<template>
    <nav class="navbar">
      <div class="navbar-container">
        <!-- ✅ Brand Logo -->
        <router-link to="/" class="logo">Flex<span>Work</span></router-link>
  
        <!-- ✅ Navigation Links -->
        <ul class="nav-links">
          <li><router-link to="/">Home</router-link></li>
          <li><router-link to="/my-jobs">My Jobs</router-link></li>
          <li v-if="isAuthenticated"><router-link to="/profile">Profile</router-link></li>
        </ul>

        <button v-if="isClientOrAdmin"  @click="goToListJob">List Job</button>
  
        <!-- ✅ Authentication Buttons -->
        <div class="auth-buttons">
          <button v-if="!isAuthenticated" @click="goToLogin">Login</button>
          <button v-if="isAuthenticated" @click="logout">Logout</button>
        </div>
      </div>
    </nav>
  </template>
  
  <script>
  import { useAuthStore } from "@/stores/authStore";
  export default {
    setup() {
      const authStore = useAuthStore();
      return { authStore };
    },
    
    name: "NavbarComponent",
    computed: {
      isAuthenticated() {
        return !!localStorage.getItem("jwt");
      },
      isFreelancer() {
        return this.authStore.isFreelancer;
      },
      isClientOrAdmin() {
        return this.authStore.isClientOrAdmin;
      },
        
    },
    methods: {
      goToLogin() {
        this.$router.push("/login");
      },
      logout() {
        this.authStore.logout();
        this.$router.push("/login");
      },
      goToListJob(){
        this.$router.push("/list-job");
      }
      
    },
  };
  </script>
  
  <style scoped>
  /* ✅ Navbar Container */
  .navbar {
    background: #222831;
    padding: 15px 30px;
    display: flex;
    justify-content: center;
    box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.3);
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 100;
  }
  
  /* ✅ Navbar Layout */
  .navbar-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    max-width: 1200px;
  }
  
  /* ✅ Brand Logo */
  .logo {
    font-size: 24px;
    font-weight: bold;
    color: #00ADB5;
    text-decoration: none;
  }
  
  .logo span {
    color: #EEEEEE;
  }
  
  /* ✅ Navigation Links */
  .nav-links {
    list-style: none;
    display: flex;
    gap: 20px;
  }
  
  .nav-links li a {
    color: #EEEEEE;
    text-decoration: none;
    font-size: 16px;
    padding: 10px 15px;
    transition: 0.3s;
  }
  
  .nav-links li a:hover {
    background: #00ADB5;
    border-radius: 5px;
  }
  
  /* ✅ Authentication Buttons */
  .auth-buttons button {
    background: #00ADB5;
    border: none;
    padding: 8px 15px;
    color: white;
    font-size: 14px;
    cursor: pointer;
    border-radius: 5px;
    transition: 0.3s;
  }
  
  .auth-buttons button:hover {
    background: #008C94;
  }

  button {
    background: #00ADB5;
    border: none;
    padding: 8px 15px;
    color: white;
    font-size: 14px;
    cursor: pointer;
    border-radius: 5px;
    transition: 0.3s;
  }
  </style>
  
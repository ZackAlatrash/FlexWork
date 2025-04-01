<template>
  <div id="app">
    <NavbarComponent v-if="showNavbar" />
    <div class="content" :style="{ paddingTop: navbarPadding }">
      <router-view />
    </div>
  </div>
</template>

<script>
import { useAuthStore } from "@/stores/authStore";
import NavbarComponent from "./components/NavbarComponent.vue";
import { jwtDecode } from "jwt-decode"; 


export default {
  components: {
    NavbarComponent,
  },
  setup() {
    const authStore = useAuthStore();
    authStore.decodeToken(); 
    return { authStore };
  },
  data() {
    return {
      isFreelancer: false,
      isClientOrAdmin: false,
    };
  },
  computed: {
    showNavbar() {
      return !["/login", "/signup"].includes(this.$route.path);
    },
    navbarPadding() {
      return this.showNavbar ? "60px" : "0px"; 
    }
  },

  methods: {
  getUserRole() {
    const token = localStorage.getItem("jwt");
    if (token) {
      try {
        const decoded = jwtDecode(token);
        const userRole = decoded.role;

        this.isFreelancer = userRole === "freelancer";
        this.isClientOrAdmin = userRole === "client" || userRole === "admin";

      } catch (error) {
        console.error("Error decoding token:", error);
      }
    }
  }
},
  mounted() {
  this.getUserRole();
  }
};
</script>

<style>
* {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>

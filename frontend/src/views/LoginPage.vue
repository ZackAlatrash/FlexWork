<template>
  <div class="login-container">
    <div class="login-box">
      <h2>Welcome to <span class="brand">FlexWork</span></h2>
      <p class="subtitle">Sign in to connect freelancers & clients</p>

      <form @submit.prevent="login">
        <div class="input-group">
          <label>Email</label>
          <input type="email" v-model="email" placeholder="Enter your email" required />
        </div>
        <div class="input-group">
          <label>Password</label>
          <input type="password" v-model="password" placeholder="Enter your password" required />
        </div>

        <button type="submit" class="login-btn">Login</button>

        <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>

        <p class="register-text">
          Don't have an account? <a @click="signup">Sign up</a>
        </p>
      </form>
    </div>
  </div>
</template>

<script>
import api from "@/api/api";
import { useAuthStore } from "@/stores/authStore";
import { useRouter } from "vue-router";

export default {
  name: "LoginPage",
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();

    return {
      authStore,
      router,
      email: "",
      password: "",
      errorMessage: "",
    };
  },
  methods: {
    async login() {
      try {
        const response = await api.post("/api/users/login", {
          email: this.email,
          password: this.password,
        });

        this.authStore.setToken(response.data.token);
        console.log(response.data.token);

        console.log("Login successful:", response.data);

        this.router.push(this.$route.query.redirect || "/");
      } catch (error) {
        this.errorMessage = "Invalid email or password";
        console.error(error);
      }
    },

    signup() {
      this.router.push("/signup");
    },
  },
};
</script>

<style scoped>
/* ✅ Background Styling */
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #00ADB5, #222831);
}

/* ✅ Login Box */
.login-box {
  background: #eeeeee;
  padding: 40px;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  text-align: center;
  max-width: 400px;
  width: 100%;
  color: #222831;
}

/* ✅ Brand Styling */
h2 {
  font-size: 26px;
  font-weight: bold;
}

.brand {
  color: #00ADB5;
}

/* ✅ Subtitle */
.subtitle {
  font-size: 14px;
  color: #555;
  margin-bottom: 20px;
}

/* ✅ Input Fields */
.input-group {
  text-align: left;
  margin-bottom: 15px;
}

.input-group label {
  display: block;
  margin-bottom: 5px;
  font-size: 14px;
  font-weight: bold;
  color: #222831;
}

.input-group input {
  width: 92%;
  padding: 12px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 5px;
  background: #ffffff;
  color: #222;
  outline: none;
  transition: 0.3s;
}

.input-group input:focus {
  border-color: #00ADB5;
  box-shadow: 0 0 8px rgba(0, 173, 181, 0.4);
}

/* ✅ Login Button */
.login-btn {
  width: 100%;
  padding: 12px;
  background: #00ADB5;
  border: none;
  border-radius: 5px;
  font-size: 16px;
  color: white;
  cursor: pointer;
  transition: 0.3s;
}

.login-btn:hover {
  background: #008C94;
}

/* ✅ Sign Up Link */
.register-text {
  font-size: 14px;
  margin-top: 15px;
}

.register-text a {
  color: #00ADB5;
  text-decoration: none;
  font-weight: bold;
  cursor: pointer;
}

.register-text a:hover {
  text-decoration: underline;
}

/* ✅ Error Text */
.error-text {
  color: red;
  margin-top: 10px;
  font-size: 14px;
}
</style>

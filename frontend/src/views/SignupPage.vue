<template>
    <div class="signup-container">
      <div class="signup-box">
        <h2>Create an Account</h2>
        <form>
          <div class="input-group">
            <label>Name</label>
            <input type="text" v-model="name" placeholder="Enter your name" required>
          </div>
          <div class="input-group">
            <label>Email</label>
            <input type="email" v-model="email" placeholder="Enter your email" required>
          </div>
          <div class="input-group">
            <label>Password</label>
            <input type="password" v-model="password" placeholder="Enter your password" required>
          </div>
          <div class="input-group">
            <label>Role</label>
            <select v-model="role">
              <option value="client">Client</option>
              <option value="freelancer">Freelancer</option>
            </select>
          </div>
          <button @click.prevent="register" class="signup-btn">Sign Up</button>
          <p class="login-text">
            Already have an account? <router-link to="/login">Login</router-link>
          </p>
          <p class="error-message">{{ errorMessage }}</p>
        </form>
      </div>
    </div>
  </template>

  <script>
  import api from "@/api/api";
  export default{
    name: "SignupPage",
    data(){
      return{
        name: "",
        email: "",
        password: "",
        role: "",
        errorMessage: "",
      };
    },
    methods: {
      async register(){
        try{
            if (!this.role) {
                this.errorMessage = "Please select a role before signing up.";
                return;
            }
          const response = await api.post("/api/users/register", {
            name: this.name,
            email: this.email,
            password: this.password,
            role: this.role,
          });
          console.log("Registration successful:", response.data);
          this.$router.push("/login");
        } catch (error){
          this.errorMessage = error.response.data.message;
          console.error(error);
        }
      },
    },
  }



</script>
  
  <style scoped>
  .signup-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: #1e1e2e;
  }
  
  .signup-box {
    background: #282a36;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    text-align: center;
    max-width: 400px;
    width: 100%;
    color: #ffffff;
  }
  
  .signup-box h2 {
    margin-bottom: 20px;
    font-size: 24px;
  }
  
  .input-group {
    text-align: left;
    margin-bottom: 15px;
  }
  
  .input-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
    color: #bbb;
  }
  
  .input-group input, .input-group select {
    width: 95%;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #444;
    border-radius: 5px;
    background: #3a3b3c;
    color: #fff;
    outline: none;
  }
  
  .input-group input:focus {
    border-color: #6d28d9;
  }
  
  .signup-btn {
    width: 100%;
    padding: 10px;
    background: #6d28d9;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    color: white;
    cursor: pointer;
    transition: 0.3s;
  }
  
  .signup-btn:hover {
    background: #5a21c1;
  }
  
  .login-text {
    font-size: 14px;
    margin-top: 15px;
  }
  
  .login-text a {
    color: #6d28d9;
    text-decoration: none;
  }
  
  .login-text a:hover {
    text-decoration: underline;
  }
  
  .error-message {
    color: red;
    font-size: 14px;
    margin-top: 10px;
  }
  </style>
  
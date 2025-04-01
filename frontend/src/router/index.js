import { jwtDecode } from "jwt-decode"; 
import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import LoginPage from "../views/LoginPage.vue";
import SignupPage from "../views/SignupPage.vue";
import JobDetails from "../views/JobDetails.vue";
import ListJobPage from "../views/ListJobPage.vue";
import MyJobsPage from "../views/MyJobsPage.vue";
import JobMessages from "../views/JobMessages.vue";
import ReviewPage from "../views/ReviewPage.vue";
import ProfilePage from "../views/ProfilePage.vue";



const routes = [
  { path: "/", component: HomeView, meta: { requiresAuth: true } },
  { path: "/login", component: LoginPage },
  { path: "/signup", component: SignupPage },
  { path: "/jobs/:id", component: JobDetails, meta: { requiresAuth: true }, },
  { path: "/list-job", component: ListJobPage, meta: { requiresAuth: true }, },
  { path: "/my-jobs", component: MyJobsPage, meta: { requiresAuth: true }, },
  { path: "/job/messages", component: JobMessages, meta: { requiresAuth: true }, },
  { path: "/freelancer/:id/reviews", component: ReviewPage, meta: { requiresAuth: true }, },
  { path: "/profile", component: ProfilePage, meta: { requiresAuth: true }, },

];

const router = createRouter({
  history: createWebHistory(),
  routes
});



router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("jwt");
  let isAuthenticated = false;

  if (token) {
    try {
      const decodedToken = jwtDecode(token);
      const isTokenExpired = decodedToken.exp * 1000 < Date.now();

      if (isTokenExpired) {
        localStorage.removeItem("jwt");
        isAuthenticated = false;
      } else {
        isAuthenticated = true;
      }
    } catch (error) {
      console.error("Invalid token", error);
      localStorage.removeItem("jwt");
    }
  }

  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ path: "/login", query: { redirect: to.fullPath } });
  } else if (to.path === "/login" && isAuthenticated) {
    next("/");
  } else {
    next();
  }
});




export default router;

// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import DefaultLayout from '../components/DefaultLayout.vue'
import HomePage from '../views/HomePage.vue'
import LoginPage from '../views/LoginPage.vue'
import ManageStudentEnrollmentPage from '../views/ManageStudentEnrollmentPage.vue'
import ComponentMarksPage from '../views/ComponentMarksPage.vue'
import AddFinalExamMarksPage from '../views/AddFinalExamMarksPage.vue'
import ViewFullMarkBreakdownPage from '../views/ViewFullMarkBreakdownPage.vue'
import AddPerformanceTrendPage from '../views/AddPerformanceTrendPage.vue'
import CoursePage from '../views/CoursePage';
import SectionPage from '../views/SectionPage';

const routes = [
  {
    path: '/',
    component: DefaultLayout,
    children: [
      { path: '/', name: 'Home', component: HomePage },
      { path: '/enroll', name: 'Enroll', component: ManageStudentEnrollmentPage },
      { path: '/manage-component-marks', name: 'ComponentMarks', component: ComponentMarksPage },
      { path: '/add-final-exam-marks', name: 'AddFinalExamMarks', component: AddFinalExamMarksPage },
      { path: '/mark-breakdown', name: 'ViewFullMarkBreakdown', component: ViewFullMarkBreakdownPage },
      { path: '/performance-trend', name: 'AddPerformanceTrend', component: AddPerformanceTrendPage },
      { path: '/course-management', name: 'CoursePage', component: CoursePage },
      { path: '/course-management/section/:courseId', name: 'SectionPage', component: SectionPage },
    ]
  },
  {
    path: '/login',
    component: LoginPage,  
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// router.beforeEach((to, from, next) => {
//   //const sessionData = sessionStorage.getItem('utmwebfc_session'); 

//   //if (!sessionData && to.path !== '/login') {
//     next({ path: '/home' });
//   //} else {
//   //  next();
//   //}
// });

export default router
// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import DefaultLayout from '../components/DefaultLayout.vue'
import HomePage from '../views/HomePage.vue'
import LoginPage from '../views/LoginPage.vue'
import StudentEnrollmentPage from '../views/StudentEnrollmentPage.vue'
import ComponentMarksPage from '../views/ComponentMarksPage.vue'
import AddFinalExamMarksPage from '../views/AddFinalExamMarksPage.vue'
import ViewFullMarkBreakdownPage from '../views/ViewFullMarkBreakdownPage.vue'
import AddPerformanceTrendPage from '../views/AddPerformanceTrendPage.vue'
import CoursePage from '../views/CoursePage';
import SectionPage from '../views/SectionPage';
import UserPage from '../views/UserPage';
import LecturerCoursePage from '../views/LecturerCoursePage'
import ViewMarkBreakdownPage from '../views/ViewMarkBreakdownPage'
import StudentDashboardPage from '../views/StudentDashboardPage.vue'
import StudentDashboardLayout from '../components/StudentDashboardLayout.vue'
import AdvisorDashboardLayout from '../components/AdvisorDashboardLayout.vue'


const routes = [
  {
    path: '/',
    component: DefaultLayout,
    children: [
      { path: '/', name: 'Home', component: HomePage },
      { path: '/manage-component-marks', name: 'ComponentMarks', component: ComponentMarksPage },
      { path: '/add-final-exam-marks', name: 'AddFinalExamMarks', component: AddFinalExamMarksPage },
      { path: '/mark-breakdown', name: 'ViewFullMarkBreakdown', component: ViewFullMarkBreakdownPage },
      { path: '/performance-trend', name: 'AddPerformanceTrend', component: AddPerformanceTrendPage },
      { path: '/course-management', name: 'CoursePage', component: CoursePage },
      { path: '/course-management/section/:courseId', name: 'SectionPage', component: SectionPage },
      { path: '/user-management', name: 'UserPage', component: UserPage },
      { path: '/lecturer-course-management', name: 'LecturerCoursePage', component: LecturerCoursePage },
      { path: '/lecturer-course-management/students/:sectionId', name: 'StudentEnrollment', component: StudentEnrollmentPage },
      { path: '/view-mark-breakdown', name: 'ViewMarkBreakdownPage', component: ViewMarkBreakdownPage },
    ]
  },

  {
    path: '/',
    component: StudentDashboardLayout,
    children: [
      { path: '/student-dashboard', name: 'StudentDashboard', component: StudentDashboardPage },
      { path: '/student-marks', name: 'StudentMarks', component: () => import('../views/StudentMarksPage.vue') },
      { path: '/student-compare', name: 'StudentCompare', component: () => import('../views/StudentComparePage.vue') },
      { path: '/student-rank', name: 'StudentRank', component: () => import('../views/StudentRankPage.vue') },
      { path: '/student-component-averages', name: 'StudentComponentAverages', component: () => import('../views/StudentComponentAveragePage.vue') },
      { path: '/student-remark', name: 'StudentRemark', component: () => import('../views/StudentRemarkPage.vue') },
      
    ]
  },
  {
    path: '/',
    component: AdvisorDashboardLayout,
    children: [
      { path: '/advisor-dashboard', name: 'AdvisorDashboard', component: { template: '<div style="padding:2rem"><h2>Advisor Dashboard</h2><p>Welcome to the advisor dashboard.</p></div>' } },
      { path: '/advisor-advisees', name: 'AdvisorAdvisees', component: () => import('../views/AdvisorAdviseesPage.vue') },
      { path: '/advisor-advisee-marks', name: 'AdvisorAdviseeMarks', component: () => import('../views/AdvisorAdviseeMarksPage.vue') },
      { path: '/advisor-compare', name: 'AdvisorCompare', component: () => import('../views/AdvisorComparePage.vue') },
      { path: '/advisor-rank', name: 'AdvisorRank', component: () => import('../views/AdvisorRankPage.vue') },
      { path: '/advisor-component-averages', name: 'AdvisorComponentAverages', component: () => import('../views/AdvisorComponentAveragePage.vue') },
      { path: '/advisor-student-remarks', name: 'AdvisorStudentRemarks', component: () => import('../views/AdvisorStudentRemarksPage.vue') },
      { path: '/advisor/advisee-overall-performance', name: 'AdvisorAdviseeOverallPerformance', component: () => import('../views/AdvisorAdviseeOverallPerformancePage.vue') },
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
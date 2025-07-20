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
import ComponentMarkPage from '../views/ComponentMarkPage.vue';
import StudentDashboardPage from '../views/StudentDashboardPage.vue'
import LecturerRemarkRequestsPage from '../views/LecturerRemarkRequestsPage.vue';

const routes = [
  {
    path: '/',
    component: DefaultLayout,
    children: [
      { path: '/', name: 'Home', component: HomePage, meta: { roles: ['Admin', 'Lecturer', 'Advisor', 'Student'] } },
      { path: '/manage-component-marks', name: 'ComponentMarks', component: ComponentMarksPage, meta: { roles: ['Lecturer'] } },
      { path: '/add-final-exam-marks', name: 'AddFinalExamMarks', component: AddFinalExamMarksPage, meta: { roles: ['Lecturer'] } },
      { path: '/mark-breakdown', name: 'ViewFullMarkBreakdown', component: ViewFullMarkBreakdownPage, meta: { roles: ['Lecturer'] } },
      { path: '/performance-trend', name: 'AddPerformanceTrend', component: AddPerformanceTrendPage, meta: { roles: ['Lecturer'] } },
      { path: '/course-management', name: 'CoursePage', component: CoursePage, meta: { roles: ['Admin'] } },
      { path: '/course-management/section/:courseId', name: 'SectionPage', component: SectionPage, meta: { roles: ['Admin'] } },
      { path: '/user-management', name: 'UserPage', component: UserPage, meta: { roles: ['Admin'] } },
      { path: '/lecturer-course-management', name: 'LecturerCoursePage', component: LecturerCoursePage, meta: { roles: ['Lecturer'] } },
      { path: '/lecturer-course-management/students/:sectionId', name: 'StudentEnrollment', component: StudentEnrollmentPage, meta: { roles: ['Lecturer'] } },
      { path: '/view-mark-breakdown', name: 'ViewMarkBreakdownPage', component: ViewMarkBreakdownPage, meta: { roles: ['Lecturer', 'Student', 'Advisor'] } },
      { path: '/component/:componentId', name: 'ComponentMarkPage', component: ComponentMarkPage, meta: { roles: ['Lecturer'] }},
      { path: '/remark-requests', name: 'LecturerRemarkRequests', component: LecturerRemarkRequestsPage, meta: { roles: ['Lecturer'] } },
    ]
  },

  {
    path: '/',
    component: DefaultLayout,
    children: [
      { path: '/student-dashboard', name: 'StudentDashboard', component: StudentDashboardPage, meta: { roles: ['Student'] } },
      { path: '/student-marks', name: 'StudentMarks', component: () => import('../views/StudentMarksPage.vue'), meta: { roles: ['Student'] } },
      { path: '/student-compare', name: 'StudentCompare', component: () => import('../views/StudentComparePage.vue'), meta: { roles: ['Student'] } },
      { path: '/student-rank', name: 'StudentRank', component: () => import('../views/StudentRankPage.vue'), meta: { roles: ['Student'] } },
      { path: '/student-component-averages', name: 'StudentComponentAverages', component: () => import('../views/StudentComponentAveragePage.vue'), meta: { roles: ['Student'] } },
      { path: '/student-remark', name: 'StudentRemark', component: () => import('../views/StudentRemarkPage.vue'), meta: { roles: ['Student'] } },
    ]
  },
  {
    path: '/',
    component: DefaultLayout,
    children: [
      { path: '/advisor-advisees', name: 'AdvisorAdvisees', component: () => import('../views/AdvisorAdviseesPage.vue'), meta: { roles: ['Advisor'] } },
      { path: '/advisor-advisee-marks', name: 'AdvisorAdviseeMarks', component: () => import('../views/AdvisorAdviseeMarksPage.vue'), meta: { roles: ['Advisor'] } },
      { path: '/advisor-compare', name: 'AdvisorCompare', component: () => import('../views/AdvisorComparePage.vue'), meta: { roles: ['Advisor'] } },
      { path: '/advisor-rank', name: 'AdvisorRank', component: () => import('../views/AdvisorRankPage.vue'), meta: { roles: ['Advisor'] } },
      { path: '/advisor-component-averages', name: 'AdvisorComponentAverages', component: () => import('../views/AdvisorComponentAveragePage.vue'), meta: { roles: ['Advisor'] } },
      { path: '/advisor-student-remarks', name: 'AdvisorStudentRemarks', component: () => import('../views/AdvisorStudentRemarksPage.vue'), meta: { roles: ['Advisor'] } },
      { path: '/advisor/advisee-overall-performance', name: 'AdvisorAdviseeOverallPerformance', component: () => import('../views/AdvisorAdviseeOverallPerformancePage.vue'), meta: { roles: ['Advisor'] } },
    ]
  },
  {
    path: '/login',
    component: LoginPage,  
    meta: { public: true }
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// router.beforeEach((to, from, next) => {
//   //const sessionData = sessionStorage.getItem('utmwebfc_session'); 

// Global navigation guard for authentication and role-based access
router.beforeEach((to, from, next) => {
  const publicPage = to.matched.some(record => record.meta.public);
  const jwt = sessionStorage.getItem('jwt');
  const user = sessionStorage.getItem('user');

  console.log(user);

  if (publicPage) {
    return next();
  }

   if (!jwt || !user) {
    // If coming from a protected page, show logout message
    if (from.path !== '/login') {
      console.log('Session expired or user logged out');
    }
    return next({ path: '/login' });
  }

  const userRoles = JSON.parse(user).roles.map(r => r.toLowerCase());
  const allowedRoles = to.matched.reduce((roles, record) => {
    if (record.meta && record.meta.roles) {
      return roles.concat(record.meta.roles.map(r => r.toLowerCase()));
    }
    return roles;
  }, []);

  if (allowedRoles.length > 0 && !userRoles.some(role => allowedRoles.includes(role))) {
    return next({ path: '/' });
  }
  next();
});

//   //if (!sessionData && to.path !== '/login') {
//     next({ path: '/home' });
//   //} else {
//   //  next();
//   //}
// });

export default router
// src/router/index.js

// Group 4:

// 1. KUAN JI TONG (A22EC0062)
// 2. TAM JIA HAO (A22EC0106)
// 3. TAN YOU CHUN (A22EC0108)
// 4. KHAIRUL AZHAR BIN ZUHRY (A20EC3001)

import { createRouter, createWebHistory } from 'vue-router'
import { jwtDecode } from 'jwt-decode';

import DefaultLayout from '../components/DefaultLayout.vue'
import HomePage from '../views/HomePage.vue'
import LoginPage from '../views/LoginPage.vue'
import StudentEnrollmentPage from '../views/StudentEnrollmentPage.vue'
import ComponentMarksPage from '../views/ComponentMarksPage.vue'
import AddFinalExamMarksPage from '../views/AddFinalExamMarksPage.vue'
import CoursePage from '../views/CoursePage';
import SectionPage from '../views/SectionPage';
import UserPage from '../views/UserPage';
import LecturerCoursePage from '../views/LecturerCoursePage'
import ViewMarkBreakdownPage from '../views/ViewMarkBreakdownPage'
import ComponentMarkPage from '../views/ComponentMarkPage.vue';
import LecturerRemarkRequestsPage from '../views/LecturerRemarkRequestsPage.vue';

const routes = [
  {
    path: '/',
    component: DefaultLayout,
    children: [
      { path: '/', name: 'Home', component: HomePage, meta: { roles: ['Admin', 'Lecturer', 'Advisor', 'Student'] } },
      { path: '/manage-component-marks', name: 'ComponentMarks', component: ComponentMarksPage, meta: { roles: ['Lecturer'] } },
      { path: '/add-final-exam-marks', name: 'AddFinalExamMarks', component: AddFinalExamMarksPage, meta: { roles: ['Lecturer'] } },
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

router.beforeEach((to, from, next) => {
  const isPublic = to.matched.some(record => record.meta.public)
  const jwt = sessionStorage.getItem('jwt')
  const user = sessionStorage.getItem('user')

  if (isPublic) return next()

  if (!jwt || !user) {
    sessionStorage.clear()
    return next('/login')
  }

  try {
    const decoded = jwtDecode(jwt) 
    const now = Date.now() / 1000
    if (decoded.exp < now) {
      console.log('Token expired')
      sessionStorage.clear()
      return next('/login')
    }

    const userRoles = JSON.parse(user).roles.map(r => r.toLowerCase())
    const allowedRoles = to.matched.reduce((roles, record) => {
      if (record.meta?.roles) {
        roles.push(...record.meta.roles.map(r => r.toLowerCase()))
      }
      return roles
    }, [])

    if (allowedRoles.length && !userRoles.some(role => allowedRoles.includes(role))) {
      return next('/')
    }

    return next()
  } catch (err) {
    console.error('Invalid JWT', err)
    sessionStorage.clear()
    return next('/login')
  }
})

export default router

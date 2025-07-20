<template>
  <div class="home-dashboard">
    <h1>Home</h1>

    <div v-if="userInfo" class="welcome-section">
      <h2>Welcome, {{ userInfo.name }}!</h2>
    </div>

    <div class="dashboard-grid">
      <!-- Student Cards -->
      <template v-if="isStudent">
        <div
          class="dashboard-card"
          v-for="(item, i) in studentCards"
          :key="'student-' + i"
        >
          <h3>{{ item.title }}</h3>
          <router-link :to="item.link">{{ item.linkText }}</router-link>
        </div>
      </template>

      <!-- Admin Cards -->
      <template v-if="isAdmin">
        <div
          class="dashboard-card"
          v-for="(item, i) in adminCards"
          :key="'admin-' + i"
        >
          <h3>{{ item.title }}</h3>
          <router-link :to="item.link">{{ item.linkText }}</router-link>
        </div>
      </template>

      <!-- Lecturer and Advisor both -->
      <template v-if="isLecturer && isAdvisor">
        <div class="section-label">Lecturer Section</div>
        <div
          class="dashboard-card"
          v-for="(item, i) in lecturerCards"
          :key="'lecturer-' + i"
        >
          <h3>{{ item.title }}</h3>
          <router-link :to="item.link">{{ item.linkText }}</router-link>
        </div>

        <div class="section-label">Academic Advisor Section</div>
        <div
          class="dashboard-card"
          v-for="(item, i) in advisorCards"
          :key="'advisor-' + i"
        >
          <h3>{{ item.title }}</h3>
          <router-link :to="item.link">{{ item.linkText }}</router-link>
        </div>
      </template>

      <!-- Lecturer only -->
      <template v-else-if="isLecturer">
        <div
          class="dashboard-card"
          v-for="(item, i) in lecturerCards"
          :key="'lecturer-' + i"
        >
          <h3>{{ item.title }}</h3>
          <router-link :to="item.link">{{ item.linkText }}</router-link>
        </div>
      </template>

      <!-- Advisor only -->
      <template v-else-if="isAdvisor">
        <div
          class="dashboard-card"
          v-for="(item, i) in advisorCards"
          :key="'advisor-' + i"
        >
          <h3>{{ item.title }}</h3>
          <router-link :to="item.link">{{ item.linkText }}</router-link>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  name: "HomePage",
  data() {
    const user = JSON.parse(sessionStorage.getItem("user"));
    const roles = user?.roles?.map((r) => r.toLowerCase()) || [];

    return {
      userInfo: user,
      userRoles: roles,

      studentCards: [
        { title: "View Component-wise Marks & Total", link: "/student-marks", linkText: "Go to My Marks" },
        { title: "Compare with Coursemates", link: "/student-compare", linkText: "Compare Now" },
        { title: "Class Rank & Percentile", link: "/student-rank", linkText: "View Rank" },
        { title: "Component Average", link: "/student-component-averages", linkText: "Simulate Performance" },
        { title: "Submit Remark Request", link: "/student-remark", linkText: "Request Remark" }
      ],

      lecturerCards: [
        { title: "My Courses", link: "/lecturer-course-management", linkText: "Manage Courses" },
        { title: "Manage Component Marks", link: "/manage-component-marks", linkText: "Enter Component Marks" },
        { title: "Final Exam Entry", link: "/add-final-exam-marks", linkText: "Submit Final Marks" },
        { title: "View Mark Breakdown", link: "/view-mark-breakdown", linkText: "View Breakdown" },
        { title: "Remark Requests", link: "/remark-requests", linkText: "Handle Remarks" }
      ],

      advisorCards: [
        { title: "My Advisees", link: "/advisor-advisees", linkText: "View Advisees" },
        { title: "Compare Advisees", link: "/advisor-compare", linkText: "Start Comparison" },
        { title: "Advisee Ranking", link: "/advisor-rank", linkText: "View Ranking" },
        { title: "Component Averages", link: "/advisor-component-averages", linkText: "View Averages" },
        { title: "Student Remarks", link: "/advisor-student-remarks", linkText: "See Remarks" },
        { title: "Overall Performance", link: "/advisor/advisee-overall-performance", linkText: "Monitor Performance" }
      ],

      adminCards: [
        { title: "Course Management", link: "/course-management", linkText: "Manage Courses" },
        { title: "User Management", link: "/user-management", linkText: "Manage Users" }
      ]
    };
  },
  computed: {
    isStudent() {
      return this.userRoles.includes("student");
    },
    isLecturer() {
      return this.userRoles.includes("lecturer");
    },
    isAdvisor() {
      return this.userRoles.includes("advisor");
    },
    isAdmin() {
      return this.userRoles.includes("admin");
    }
  },
  mounted() {
    if (!this.userInfo) {
      console.error("Authentication required.");
      this.$router.push("/login");
    }
  }
};
</script>

<style scoped>
.section-label {
  grid-column: 1 / -1;
  font-size: 1.25rem;
  font-weight: bold;
  margin: 24px 0 8px;
  color: #444;
}

.home-dashboard {
  padding: 32px;
}
.welcome-section {
  background: #e5e5e5;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 24px;
}
.welcome-section h2 {
  margin: 0 0 8px 0;
  color: #333;
}
.welcome-section p {
  margin: 0;
  color: #666;
}
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 24px;
  margin-top: 32px;
}
.dashboard-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
  padding: 24px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  transition: box-shadow 0.2s;
}
.dashboard-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.13);
}
h3 {
  margin-bottom: 12px;
}
router-link {
  color: #0d6efd;
  text-decoration: underline;
  font-weight: 500;
}
</style>


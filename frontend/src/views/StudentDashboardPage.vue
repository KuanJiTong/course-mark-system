<template>
  <div class="student-dashboard">
    <h1>Student Dashboard</h1>
    <div v-if="userInfo" class="welcome-section">
      <h2>Welcome, {{ userInfo.name }}!</h2>
      <p>Student ID: {{ userInfo.user_id }}</p>
    </div>
    <div class="dashboard-grid">
      <div class="dashboard-card">
        <h3>View Component-wise Marks & Total</h3>
        <router-link to="/student-marks">Go to My Marks</router-link>
      </div>
      <div class="dashboard-card">
        <h3>Progress Bar & Assessment Breakdown</h3>
        <router-link to="/student-progress">View Progress</router-link>
      </div>
      <div class="dashboard-card">
        <h3>Compare with Coursemates</h3>
        <router-link to="/student-compare">Compare Now</router-link>
      </div>
      <div class="dashboard-card">
        <h3>Class Rank & Percentile</h3>
        <router-link to="/student-rank">View Rank</router-link>
      </div>
      <div class="dashboard-card">
        <h3>What-If Simulator</h3>
        <router-link to="/student-whatif">Simulate Performance</router-link>
      </div>
      <div class="dashboard-card">
        <h3>Submit Remark Request</h3>
        <router-link to="/student-remark">Request Remark</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StudentDashboardPage',
  data() {
    return {
      userInfo: null
    };
  },
  methods: {
    getAuthenticatedUser() {
      const userData = sessionStorage.getItem('user');
      if (userData) {
        this.userInfo = JSON.parse(userData);
        console.log('Authenticated student:', this.userInfo);
        return true;
      }
      return false;
    }
  },
  mounted() {
    if (!this.getAuthenticatedUser()) {
      console.error('Authentication required. Please login.');
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
.student-dashboard {
  padding: 32px;
}
.welcome-section {
  background: #f8f9fa;
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
  box-shadow: 0 2px 8px rgba(0,0,0,0.07);
  padding: 24px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  transition: box-shadow 0.2s;
}
.dashboard-card:hover {
  box-shadow: 0 4px 16px rgba(0,0,0,0.13);
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
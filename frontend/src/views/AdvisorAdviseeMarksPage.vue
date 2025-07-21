<template>
  <div>
    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1rem; padding: 30px">
      <router-link to="/advisor-advisees" class="btn btn-outline-secondary">&larr; Return to Advisee List</router-link>
      
    </div>
    <h2 h2 class="mt-4 mb-4">Full Mark Breakdown for {{ resolvedStudentName }}</h2>
    <div v-if="enrollments.length === 0 && loaded">No enrollments found.</div>
    <div v-for="enrollment in enrollments" :key="enrollment.section_id" class="course-section">
      <h3>{{ enrollment.course_code }}-{{ enrollment.section_number }} {{ enrollment.course_name }}</h3>
      <table v-if="marks[enrollment.section_id]">
        <thead>
          <tr>
            <th>Component</th>
            <th>Mark</th>
            <th>Max Mark</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="mark in marks[enrollment.section_id]" :key="mark.component_id">
            <td>{{ mark.componentName }}</td>
            <td>{{ mark.mark }}</td>
            <td>{{ mark.maxMark }}</td>
          </tr>
        </tbody>
      </table>
      <div v-else>Loading marks...</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdvisorAdviseeMarksPage',
  props: {
    studentId: String,
    studentName: String
  },
  data() {
    return {
      advisorID: null, // For reference, not used in API calls
      enrollments: [],
      marks: {},
      loaded: false,
    };
  },
  computed: {
    resolvedStudentId() {
      return this.studentId || this.$route.query.studentId;
    },
    resolvedStudentName() {
      return this.studentName || this.$route.query.studentName;
    }
  },
  methods: {
    getAuthenticatedUser() {
      const userData = sessionStorage.getItem('user');
      if (userData) {
        const user = JSON.parse(userData);
        this.advisorID = user.lecturerId; 
        console.log('Authenticated advisor (lecturer) ID for advisee marks:', this.advisorID);
        return true;
      }
      return false;
    },
    async fetchEnrollments() {
      try {
        const res = await fetch(`http://localhost:3000/student/enrollments?student_id=${this.resolvedStudentId}`);
        if (!res.ok) throw new Error('Failed to fetch enrollments');
        const enrollments = await res.json();
        this.enrollments = enrollments;
        this.loaded = true;
        enrollments.forEach(enrollment => {
          this.fetchMarks(enrollment);
        });
      } catch (error) {
        console.error('Error fetching enrollments:', error);
        this.loaded = true;
      }
    },
    async fetchMarks(enrollment) {
      try {
        const res = await fetch(`http://localhost:3000/student/marks?student_id=${this.resolvedStudentId}&course_id=${enrollment.course_id}&section_id=${enrollment.section_id}`);
        if (!res.ok) throw new Error('Failed to fetch marks');
        const data = await res.json();
        this.marks[enrollment.section_id] = Array.isArray(data.marks) ? data.marks : [];
      } catch (error) {
        console.error('Error fetching marks:', error);
        this.marks[enrollment.section_id] = [];
      }
    },
  },
  mounted() {
    this.$emit('update-active-tab', 'My Advisees');
    if (this.getAuthenticatedUser()) {
      this.fetchEnrollments();
    } else {
      console.error('Authentication required. Please login.');
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
.course-section {
  margin-bottom: 2rem;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 0.5rem;
}
th, td {
  border: 1px solid #ddd;
  padding: 0.5rem 1rem;
  text-align: left;
}
th {
  background: #f5f5f5;
}
</style> 
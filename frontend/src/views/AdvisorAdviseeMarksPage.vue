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
          <tr v-if="finalExams[enrollment.section_id]">
            <td>Final Exam</td>
            <td>{{ finalExams[enrollment.section_id].mark }}</td>
            <td>{{ finalExams[enrollment.section_id].maxFm }}</td>
          </tr>
          <tr>
            <td><b>Total</b></td>
            <td><b>{{ getTotalMark(enrollment.section_id) }}</b></td>
            <td><b>100</b></td>
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
      advisorId: null, 
      enrollments: [],
      marks: {},
      loaded: false,
      finalExams: {},
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
        this.advisorId = user.user_id; 
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
        this.finalExams[enrollment.section_id] = data.finalExam || null;
      } catch (error) {
        console.error('Error fetching marks:', error);
        this.marks[enrollment.section_id] = [];
        this.finalExams[enrollment.section_id] = null;
      }
        },
    getFinalExam(sectionId) {
      const arr = this.marks[sectionId] || [];
      const fe = arr.find(m => m.componentName === 'Final Exam');
      if (fe) return { mark: fe.mark, maxFm: fe.maxMark };
      // If not found, return null
      return null;
    },
    getTotalMark(sectionId) {
     
      const arr = this.marks[sectionId] || [];
      let total = 0;
      arr.forEach(m => { total += Number(m.mark) || 0; });
      const fe = this.finalExams[sectionId];
      if (fe && fe.mark) total += Number(fe.mark);
      return total.toFixed(2);
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
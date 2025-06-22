<template>
  <div>
    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1rem;">
      <router-link to="/advisor-advisees" style="display:inline-block;">&larr; Return to Advisee List</router-link>
      <button v-if="enrollments.length" @click="downloadCSV">Download CSV Report</button>
    </div>
    <h2>Full Mark Breakdown for {{ resolvedStudentName }}</h2>
    <div v-if="enrollments.length === 0 && loaded">No enrollments found.</div>
    <div v-for="enrollment in enrollments" :key="enrollment.section_id" class="course-section">
      <h3>{{ enrollment.course_name }} (Section {{ enrollment.section_number }})</h3>
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
            <td>{{ mark.component_name }}</td>
            <td>{{ mark.mark }}</td>
            <td>{{ mark.max_mark }}</td>
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
  mounted() {
    fetch(`http://localhost:3000/student/enrollments?student_id=${this.resolvedStudentId}`)
      .then(res => res.json())
      .then(enrollments => {
        this.enrollments = enrollments;
        this.loaded = true;
        enrollments.forEach(enrollment => {
          fetch(`http://localhost:3000/student/marks?student_id=${this.resolvedStudentId}&course_id=${enrollment.course_id}&section_id=${enrollment.section_id}`)
            .then(res => res.json())
            .then(marks => {
              this.marks[enrollment.section_id] = marks;
            });
        });
      });
        },
  methods: {
    downloadCSV() {
      // Download CSV for each course/section the advisee is enrolled in
      this.enrollments.forEach(enrollment => {
        const url = `http://localhost:3000/all_marks_csv?course_id=${enrollment.course_id}&section_id=${enrollment.section_id}`;
        window.open(url, '_blank');
      });
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
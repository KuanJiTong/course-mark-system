<template>
  <div>
    <h2 class="mt-4 mb-4">Class Average per Component</h2>
    <div class="form-group">
      <label for="section">Course Section:</label>
      <select class="form-select" v-model="selectedSectionId" @change="fetchAveragesAndMarks" required>
        <option disabled value="">-- Select Section --</option>
        <option v-for="enroll in enrollments" :key="enroll.section_id" :value="enroll.section_id">
          {{ enroll.course_code }}-{{ enroll.section_number }} {{ enroll.course_name }}
        </option>
      </select>
    </div>
    <table v-if="averages.length">
      <thead>
        <tr>
          <th>Component</th>
          <th>Class Average</th>
          <th>Max Mark</th>
          <th>Your Mark</th>
          <th>Comparison</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="comp in averages" :key="comp.component_id">
          <td>{{ comp.component_name }}</td>
          <td>{{ comp.average_mark ? Number(comp.average_mark).toFixed(2) : '-' }}</td>
          <td>{{ comp.max_mark }}</td>
          <td>{{ getStudentMark(comp.component_id) }}</td>
          <td>{{ compareToAverage(comp) }}</td>
        </tr>
      </tbody>
    </table>
    <div v-else-if="selectedSectionId && !averages.length && !errorMessage" class="info-message">
      No components found for this section.
    </div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
export default {
  data() {
    const user = JSON.parse(sessionStorage.getItem('user'));
    return {
      studentId: user.studentId,
      enrollments: [],
      averages: [],
      studentMarks: [],
      selectedSectionId: '',
      errorMessage: ''
    };
  },
  async created() {
    await this.fetchEnrollments();
  },
  methods: {
    async fetchEnrollments() {
      try {
        const res = await fetch(`http://localhost:3000/student/enrollments?student_id=${this.studentId}`);
        if (!res.ok) {
          this.errorMessage = 'Server error loading enrollments';
          return;
        }
        this.enrollments = await res.json();
        // Auto-select first section if available
        if (this.enrollments.length && !this.selectedSectionId) {
          this.selectedSectionId = this.enrollments[0].section_id;
          await this.fetchAveragesAndMarks();
        }
      } catch (err) {
        this.errorMessage = 'Failed to load enrollments.';
      }
    },
    async fetchAveragesAndMarks() {
      if (!this.selectedSectionId) return;
      await Promise.all([
        this.fetchAverages(),
        this.fetchStudentMarks()
      ]);
    },
    async fetchAverages() {
      try {
        this.averages = [];
        const url = `http://localhost:3000/class/component-averages?section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) {
          this.errorMessage = 'Failed to load averages (server error).';
          return;
        }
        this.averages = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load averages (network error).';
      }
    },
    async fetchStudentMarks() {
      try {
        this.studentMarks = [];
        const url = `http://localhost:3000/student/marks?student_id=${this.studentId}&section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) return;
        const data = await res.json();
        this.studentMarks = Array.isArray(data.marks)
          ? data.marks.map(mark => ({
              component_id: mark.componentId,
              mark: mark.mark
            }))
          : [];
      } catch (err) {
        // ignore, just show empty
      }
    },
    getStudentMark(component_id) {
      const found = this.studentMarks.find(m => String(m.component_id) === String(component_id));
      return found ? found.mark : '-';
    },
    compareToAverage(comp) {
      const mark = Number(this.getStudentMark(comp.component_id));
      const avg = Number(comp.average_mark);
      if (isNaN(mark) || isNaN(avg)) return '-';
      if (mark > avg) return 'Above Average';
      if (mark < avg) return 'Below Average';
      return 'At Average';
    }
  }
};
</script>

<style scoped>
h1 {
  font-size: 24px;
  margin-bottom: 20px;
}
.form-group {
  margin-bottom: 15px;
}
label {
  font-weight: bold;
}
select {
  width: 100%;
  padding: 8px;
  font-size: 16px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: center;
}
th {
  background-color: #f5f5f5;
}
.error-message {
  color: red;
  margin-top: 10px;
}
.info-message {
  color: #007bff;
  margin-top: 10px;
}
</style> 
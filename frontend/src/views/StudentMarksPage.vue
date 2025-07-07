<template>
  <div class="student-marks">
    <h1>My Marks</h1>
    <div class="form-group">
      <label for="course">Course:</label>
      <select class="form-select" v-model="selectedSectionId" @change="fetchMarks" required>
        <option disabled value="">-- Select Course --</option>
        <option v-for="course in courses" :key="course.sectionId" :value="course.sectionId">
          {{ course.courseCode }}-{{ course.sectionNumber }} {{ course.courseName }}
        </option>
      </select>
    </div>
    <table v-if="marks.length">
      <thead>
        <tr>
          <th>Component</th>
          <th>Mark</th>
          <th>Max Mark</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="mark in marks" :key="mark.markId">
          <td>{{ mark.componentName }}</td>
          <td>{{ mark.mark }}</td>
          <td>{{ mark.maxMark }}</td>
        </tr>
        <tr>
          <td>Final Exam</td>
          <td>{{ finalExam.mark }}</td>
          <td>{{ finalExam.maxFm }}</td>
        </tr>
        <tr>
          <td><b>Total</b></td>
          <td><b>{{ totalMark }}</b></td>
          <td><b>100</b></td>
        </tr>
      </tbody>
    </table>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
export default {
  data() {
    const user = JSON.parse(sessionStorage.getItem('user'));

    return {
      studentId: user.studentId,
      courses: [],
      marks: [],
      finalExam: null,
      totalMark: null,
      selectedSectionId: null,
      errorMessage: ''
    };
  },
  async created(){
    await this.fetchCourses();
  },
  methods: {
    async fetchCourses() {
      try {
        const res = await fetch(`http://localhost:3000/enrolled-courses?student_id=${this.studentId}`);
        if (!res.ok) {
          this.errorMessage = 'Server error loading courses';
          return;
        }
        this.courses = await res.json();
        // Auto-select first course if available
        if (this.courses.length && !this.selectedSectionId) {
          this.selectedSectionId = this.courses[0].sectionId;
          await this.fetchMarks();
        }
      } catch (err) {
        this.errorMessage = 'Failed to load courses.';
      }
    },
    async fetchMarks() {
      // Guard: Only fetch if all required params are set
      if (!this.studentId || !this.selectedSectionId) {
        this.errorMessage = 'Please select a course.';
        return;
      }
      try {
        this.marks = [];
        const url = `http://localhost:3000/student/marks?student_id=${this.studentId}&section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) {
          this.errorMessage = 'Failed to load marks (server error).';
          return;
        }
        const data = await res.json();
        this.marks = Array.isArray(data.marks) ? data.marks : [];
        this.finalExam = data.finalExam;
        this.totalMark = data.totalMark;
      } catch (err) {
        this.errorMessage = 'Failed to load marks (network error).';
      }
    }
  },
};
</script>

<style scoped>
.student-marks {
  max-width: 700px;
  margin: auto;
  padding: 20px;
}
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
</style> 
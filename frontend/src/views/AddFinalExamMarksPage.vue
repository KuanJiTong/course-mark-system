<template>
  <div class="container mt-4">
    <h3>Enter Final Exam Marks (Max: {{ maxFinalMark }}%)</h3>

    <!-- Course and Section Selection -->
    <div class="form-group mb-3">
      <label for="course">Course:</label>
      <select class="form-select" v-model="selectedSectionId" @change="fetchStudentsAndMarks" required>
        <option disabled value="">-- Select Course --</option>
        <option v-for="course in courses" :key="course.sectionId" :value="course.sectionId">
          {{ course.courseCode }}-{{ course.sectionNumber }} {{ course.courseName }}
        </option>
      </select>
    </div>

    <!-- Table of Students and Marks -->
    <table class="table table-bordered mt-3" v-if="studentMarks.length">
      <thead>
        <tr>
          <th>#</th>
          <th>Student</th>
          <th>Matric No</th>
          <th>Final Mark (max: {{ maxFinalMark }})</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(entry, index) in studentMarks" :key="entry.studentId">
          <td>{{ index + 1 }}</td>
          <td>{{ entry.studentName }}</td>
          <td>{{ entry.matricNo }}</td>
          <td>
            <input
              type="number"
              class="form-control"
              v-model.number="entry.mark"
              :max="maxFinalMark"
              min="0"
            />
          </td>
        </tr>
      </tbody>
    </table>

    <button class="btn btn-success mt-3" @click="submitAllMarks" :disabled="!studentMarks.length">
      💾 Save All
    </button>

    <div v-if="successMessage" class="success-message mt-2">{{ successMessage }}</div>
    <div v-if="errorMessage" class="error-message mt-2">{{ errorMessage }}</div>
  </div>
</template>



<script>
export default {
  data() {
    const user = JSON.parse(sessionStorage.getItem('user'));

    return {
      lecturerId: user?.lecturerId || '',
      selectedSectionId: '',
      courses: [],
      studentMarks: [],
      successMessage: '',
      errorMessage: ''
    };
  },
  computed: {
    selectedCourse() {
      return this.courses.find(c => c.sectionId === this.selectedSectionId) || {};
    },
    maxFinalMark() {
      return this.selectedCourse?.maxFm || 100;
    },
  },
  async created() {
    await this.fetchAllLecturerCourses();
  },
  methods: {
    async fetchAllLecturerCourses() {
      try {
        const res = await fetch(`http://localhost:3000/lecturer-course/${this.lecturerId}`);
        const data = await res.json();
        this.courses = data;

        if (this.courses.length && !this.selectedSectionId) {
          this.selectedSectionId = this.courses[0].sectionId;
          await this.fetchStudentsAndMarks();
        }
      } catch (err) {
        console.error("Error fetching courses", err);
      }
    },
    async fetchStudentsAndMarks() {
      try {
        const [studentsRes, marksRes] = await Promise.all([
          fetch(`http://localhost:3000/student-enrollment/${this.selectedSectionId}`),
          fetch(`http://localhost:3000/final_exam?section_id=${this.selectedSectionId}`)
        ]);

        const students = await studentsRes.json();
        const marks = await marksRes.json();

        this.studentMarks = students.map(student => {
          const existing = marks.find(m =>
            m.studentId === student.studentId
          );

          return {
            studentId: student.studentId,
            studentName: student.studentName,
            matricNo: student.matricNo,
            mark: existing ? existing.mark : ''
          };
        });

        this.successMessage = '';
        this.errorMessage = '';
      } catch (err) {
        this.errorMessage = 'Failed to load students or marks.';
        console.error(err);
      }
    },
    async submitAllMarks() {
      try {
        const payloads = this.studentMarks.map(entry => ({
          studentId: entry.studentId,
          sectionId: this.selectedSectionId,
          mark: entry.mark
        }));

        const requests = payloads.map(payload =>
          fetch('http://localhost:3000/final_exam', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          })
        );

        await Promise.all(requests);
        this.successMessage = 'All final exam marks saved successfully!';
        this.errorMessage = '';
        await this.fetchStudentsAndMarks();
      } catch (err) {
        this.errorMessage = 'Failed to save marks.';
        this.successMessage = '';
        console.error(err);
      }
    }
  }
};
</script>

<style scoped>
.final-exam-marks {
  padding: 20px;
  max-width: 600px;
  margin: auto;
}
h1 {
  font-size: 24px;
  margin-bottom: 20px;
}
.form-group {
  margin-bottom: 15px;
}
label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}
input, select {
  width: 100%;
  padding: 8px;
  font-size: 16px;
}
button {
  background-color: #007bff;
  color: white;
  padding: 10px 20px;
  font-size: 16px;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}
button:hover {
  background-color: #0056b3;
}
.success-message {
  margin-top: 15px;
  color: green;
}
.error-message {
  margin-top: 15px;
  color: red;
}
</style>

<template>
  <button class="mt-4 btn btn-secondary mb-4" @click="goBack">Back</button>
  <div class="container">
    <h3><b>{{ component?.courseCode }}-{{ component?.sectionNumber }} {{ component?.courseName }}</b></h3>
    <h3>Enter Marks for: {{ component?.componentName }} (Max: {{ component?.maxMark }})</h3>

    <table class="table table-bordered mt-3" v-if="studentMarks.length">
      <thead>
        <tr>
          <th>#</th>
          <th>Student</th>
          <th>Matric No</th>
          <th>Mark (max: {{ component?.maxMark }})</th>
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
              :max="component?.maxMark"
              min="0"
            />
          </td>
        </tr>
      </tbody>
    </table>

    <button class="btn btn-success mt-3" @click="submitAllMarks" :disabled="!studentMarks.length">
      Save All
    </button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      componentId: this.$route.params.componentId,
      component: null,
      studentMarks: []
    };
  },
  async created() {
    this.$emit('update-active-tab', 'Component Marks');
    console.log(this.componentId);
    await this.fetchComponentDetails();
    await this.fetchStudentsAndMarks();
  },
  methods: {
    async fetchComponentDetails() {
      const res = await fetch(`http://localhost:3000/components/${this.componentId}`);
      this.component = await res.json();
    },
    async fetchStudentsAndMarks() {
      try {
        const { sectionId } = this.component;

        const [studentsRes, marksRes] = await Promise.all([
          fetch(`http://localhost:3000/student-enrollment/${sectionId}`),
          fetch(`http://localhost:3000/marks?section_id=${sectionId}`)
        ]);

        const students = await studentsRes.json();
        const marks = await marksRes.json();

        this.studentMarks = students.map(student => {
          const existing = marks.find(m => m.studentId === student.studentId);

          return {
            studentId: student.studentId,
            studentName: student.studentName,
            matricNo: student.matricNo,
            mark: existing ? existing.mark : ''
          };
        });
      } catch (err) {
        alert('Failed to load students or marks.');
        console.error(err);
      }
    },
    goBack() {
      this.$router.back();
    },
    async submitAllMarks() {
      try {
        const payloads = this.studentMarks.map(entry => ({
          componentId: this.componentId,
          studentId: entry.studentId,
          mark: entry.mark
        }));

        const requests = payloads.map(payload =>
          fetch('http://localhost:3000/marks', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          })
        );

        await Promise.all(requests);
        alert('All marks saved successfully.');
        await this.fetchStudentsAndMarks();
      } catch (err) {
        alert('Failed to save marks.');
        console.error(err);
      }
    }
  }
};
</script>


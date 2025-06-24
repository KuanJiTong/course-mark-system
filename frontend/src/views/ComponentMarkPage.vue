<template>
  <div class="container mt-4">
    <h3>Enter Marks for: {{ component?.component_name }}</h3>

   

    <table class="table table-bordered mt-3">
      <thead>
        <tr>
          <th>#</th>
          <th>Student</th>
          <th>Mark (max: {{ component?.max_mark }})</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(entry, index) in studentMarks" :key="entry.student_id">
          <td>{{ index + 1 }}</td>
          <td>{{ entry.student_name }}</td>
          <td>
            <input
              type="number"
              class="form-control"
              v-model.number="entry.mark"
              :max="component?.max_mark"
            />
          </td>
        </tr>
      </tbody>
    </table>

    <button class="btn btn-success" @click="submitAllMarks">💾 Save All</button>
  </div>
</template>


<script>
export default {
  data() {
    return {
      userID: null,
      componentId: this.$route.params.componentId,
      component: null,
      studentMarks: [],
    };
  },
  methods: {
    async fetchComponentDetails() {
      const res = await fetch(`http://localhost:3000/components/${this.componentId}`);
      this.component = await res.json();
    },
    async fetchStudentsAndMarks() {
      const { course_id, section_id } = this.component;

      const [studentsRes, marksRes] = await Promise.all([
        fetch(`http://localhost:3000/students?course_id=${course_id}&section_id=${section_id}`),
        fetch(`http://localhost:3000/marks?course_id=${course_id}&section_id=${section_id}`)
      ]);

      const students = await studentsRes.json();
      const marks = await marksRes.json();

      this.studentMarks = students.map(student => {
        const match = marks.find(
        m => m.student_id === student.student_id && String(m.component_id) === String(this.componentId)
        );
        return {
          student_id: student.student_id,
          student_name: student.student_name,
          mark: match ? match.mark : ''
        };
      });
    },
    async submitAllMarks() {
      try {
        const promises = this.studentMarks.map(entry =>
          fetch('http://localhost:3000/marks', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              component_id: this.componentId,
              student_id: entry.student_id,
              mark: entry.mark
            })
          })
        );

        await Promise.all(promises);
        alert("All marks saved successfully!");
      } catch (error) {
        alert("Failed to save marks.");
        console.error(error);
      }
    }
  },
  async mounted() {
    // Check authentication
    const user = JSON.parse(sessionStorage.getItem('user'));
    if (!user || !user.user_id) {
      this.$router.push('/login?message=Please login to access component marks');
      return;
    }
    
    this.userID = user.user_id;
    console.log('Authenticated user ID for component marks:', this.userID);
    
    await this.fetchComponentDetails();
    await this.fetchStudentsAndMarks();
  }
};
</script>

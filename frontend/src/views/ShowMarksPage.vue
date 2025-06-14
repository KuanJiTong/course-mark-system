<template>
  <div class="p-4">
    <h2>All Saved Marks</h2>
    <table border="1" cellpadding="8">
      <thead>
        <tr>
          <th>Mark ID</th>
          <th>Student ID</th>
          <th>Component Name</th>
          <th>Mark</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="mark in marks" :key="mark.mark_id">
          <td>{{ mark.mark_id }}</td>
          <td>{{ mark.student_id }}</td>
          <td>{{ mark.component_name }}</td>
          <td>{{ mark.mark }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  data() {
    return {
      marks: []
    };
  },
  async mounted() {
    try {
      const res = await fetch("http://localhost:3000/marks");
      const data = await res.json();
      this.marks = data;
    } catch (err) {
      console.error("Failed to load marks:", err);
      alert("Failed to fetch marks.");
    }
  }
};
</script>

<style scoped>
table {
  width: 100%;
  border-collapse: collapse;
}
th {
  background-color: #f0f0f0;
}
td, th {
  text-align: left;
}
</style>

<template>
  <div class="component-marks-page">
    <div class="page-header">
      <h2>Continuous Assessment Components (70%)</h2>
    </div>
    <select v-model="selectedCourseId" class="input-field">
      <option disabled value="">-- Select Course --</option>
      <option v-for="course in courses" :key="course.course_id" :value="course.course_id">
        {{ course.course_name }}
      </option>
    </select>
    <!-- Component Creation Section -->
    <div class="component-form">
      <input v-model="newComponent.component_name" placeholder="Component Name" class="input-field" />
      <input type="number" v-model="newComponent.max_mark" placeholder="Max Mark" class="input-field" />
      <button @click="addComponent" class="btn add-btn">➕ Add Component</button>
    </div>

    <div class="marks-table-container" v-if="components.length">
      <table class="marks-table">
        <thead>
          <tr>
            <th>Component</th>
            <th>Max Mark</th>
            <th>Student ID</th>
            <th>Mark</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(component, index) in components" :key="index">
            <td>{{ component.component_name }}</td>
            <td>{{ component.max_mark }}</td>
            <td>
              <input v-model.number="component.student_id" class="input-field" placeholder="Enter Student ID" />
            </td>
            <td>
              <input type="number" v-model.number="component.mark" class="input-field" placeholder="Enter Mark" />
            </td>
            <td>
              <button @click="saveMark(component)" class="btn save-btn">💾 Save Mark</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      selectedCourseId: '',
      courses: [],
      newComponent: {
        component_name: '',
        max_mark: ''
      },
      components: []
    };
  },
  methods: {
    async fetchCourses() {
      try {
        const res = await fetch(`http://localhost:3000/courses`);
        const data = await res.json();
        this.courses = data;
      } catch (err) {
        console.error("Failed to fetch courses:", err);
        alert("Failed to load courses. Check your backend.");
      }
    }, // ← missing comma here

    async fetchComponents() {
      if (!this.selectedCourseId) return;
      const res = await fetch(`http://localhost:3000/components?course_id=${this.selectedCourseId}`);
      const data = await res.json();
      this.components = data.map(c => ({
        ...c,
        student_id: '',
        mark: ''
      }));
    },

    async addComponent() {
      if (!this.selectedCourseId) {
        alert("Please select a course.");
        return;
      }

      const payload = {
        course_id: this.selectedCourseId,
        component_name: this.newComponent.component_name,
        max_mark: this.newComponent.max_mark
      };

      const res = await fetch('http://localhost:3000/components', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      alert(data.message || 'Component saved');
      this.newComponent.component_name = '';
      this.newComponent.max_mark = '';
      this.fetchComponents();
    },

    async saveMark(component) {
      const payload = {
        student_id: component.student_id,
        component_id: component.component_id,
        mark: component.mark
      };

      const res = await fetch('http://localhost:3000/marks', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      alert(data.message || 'Mark saved');
    }
  },
  watch: {
    selectedCourseId(newVal) {
      if (newVal) {
        this.fetchComponents();
      }
    }
  },
  mounted() {
    this.fetchCourses();
  }
};
</script>


<style scoped>
.component-marks-page {
  padding: 20px;
}
.page-header {
  text-align: center;
  margin-bottom: 20px;
}
.component-form {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}
.input-field {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  flex: 1;
}
.btn {
  padding: 8px 16px;
  border: none;
  cursor: pointer;
  border-radius: 4px;
}
.add-btn {
  background-color: #4CAF50;
  color: white;
}
.save-btn {
  background-color: #2196F3;
  color: white;
}
.marks-table {
  width: 100%;
  border-collapse: collapse;
}
.marks-table th,
.marks-table td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: center;
}
</style>

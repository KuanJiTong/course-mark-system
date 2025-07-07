<template>
  <div class="container mt-4 component-marks-page">
    <div class="text-center mb-4">
      <h2>Continuous Assessment Components</h2>
      <div v-if="components.length" class="mt-2">
        <strong>Total Component Marks:</strong>
        {{ totalComponentMark }} /
        <span v-if="maxComponentMark">{{ maxComponentMark }}</span>
        <span v-else class="text-danger">Not Available</span>
      </div>
    </div>

    <div class="mb-3">
      <select v-model="selectedSectionId" class="form-select" @change="fetchComponents" v-if="courses.length">
        <option disabled value="">-- Select Course --</option>
        <option v-for="course in courses" :key="course.courseId" :value="course.sectionId">
          {{ course.courseCode }}-{{ course.sectionNumber }} {{ course.courseName }}
        </option>
      </select>
    </div>

    <!-- Component Creation -->
    <div class="row g-2 mb-3 align-items-end">
      <div class="col-md-5">
        <input v-model="newComponent.componentName" placeholder="Component Name" class="form-control" />
      </div>
      <div class="col-md-4">
        <input type="number" v-model="newComponent.maxMark" placeholder="Max Mark" class="form-control" />
      </div>
      <div class="col-md-3">
        <button @click="addComponent" class="btn btn-success w-100">➕ Add Component</button>
      </div>
    </div>

    <!-- Edit Mode -->
    <div v-if="editMode" class="row g-2 mb-3 align-items-end">
      <div class="col-md-5">
        <input v-model="editComponentData.componentName" class="form-control" />
      </div>
      <div class="col-md-4">
        <input v-model="editComponentData.maxMark" type="number" class="form-control" />
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button @click="updateComponent" class="btn btn-primary w-50">✅ Update</button>
        <button @click="cancelEdit" class="btn btn-secondary w-50">❌ Cancel</button>
      </div>
    </div>

    <!-- Table -->
    <div v-if="components.length" class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>Component</th>
            <th>Max Mark</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="component in components" :key="component.componentId">
            <td>{{ component.componentName }}</td>
            <td>{{ component.maxMark }}</td>
            <td class="d-flex flex-wrap justify-content-center gap-2">
              <router-link
                :to="{ name: 'ComponentMarkPage', params: { componentId: component.componentId } }"
                class="btn btn-outline-primary btn-sm"
              >
                ➡ Enter Marks
              </router-link>
              <button @click="editComponent(component)" class="btn btn-outline-warning btn-sm">✏ Edit</button>
              <button @click="deleteComponent(component.componentId)" class="btn btn-outline-danger btn-sm">🗑 Delete</button>
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
    const user = JSON.parse(sessionStorage.getItem('user'));

    return {
      lecturerId: user.lecturerId,
      selectedSectionId: null,
      courses: [],
      newComponent: {
        componentName: '',
        maxMark: ''
      },
      selectedCourse: null,
      components: [],
      editMode: false,
      editComponentData: {
        componentId: null,
        componentName: '',
        maxMark: ''
      }
    };
  },

  computed: {
    maxComponentMark() {
      const cm = this.selectedCourse?.max_cm;
      return cm !== null && cm !== undefined ? cm : null;
    },
      totalComponentMark() {
        return this.components.reduce((sum, c) => {
          const mark = parseFloat(c.max_mark);
          return sum + (isNaN(mark) ? 0 : mark);
        }, 0);
      }
  },
  async created(){
    await this.fetchAllLecturerCourses();
  },
  methods: {
    async fetchAllLecturerCourses(){
      try {
        if (!this.lecturerId) {
          console.error('No lecturer ID available');
          return;
        }
        
        const url = `http://localhost:3000/lecturer-course/${this.lecturerId}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch courses');

        const data = await response.json();
        this.courses = data; 
        if (this.courses.length && !this.selectedSectionId) {
          this.selectedSectionId = this.courses[0].sectionId;
          await this.fetchComponents();
        }
      } catch (error) {
        console.error('Error fetching courses:', error);
      }
    },
    async fetchComponents(){
      try {
        const url = `http://localhost:3000/components?section_id=${this.selectedSectionId}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch components');

        const data = await response.json();
        this.components = data; 
      } catch (error) {
        console.error('Error fetching components:', error);
      }
    },
    async addComponent() {
      if (!this.selectedSectionId) return;

      const payload = {
        sectionId: this.selectedSectionId,  
        componentName: this.newComponent.componentName,
        maxMark: this.newComponent.maxMark
      };

      try {
        const res = await fetch('http://localhost:3000/components', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        alert(data.message || 'Component saved');

        // Clear form
        this.newComponent.componentName = '';
        this.newComponent.maxMark = '';

        // Refresh component list
        await this.fetchComponents();

      } catch (error) {
        alert("Failed to add component.");
        console.error(error);
      }
    },
    async saveMark(componentId, studentId, mark) {
      const payload = {
        component_id: componentId,
        student_id: studentId,
        mark: mark
      };

      const res = await fetch('http://localhost:3000/marks', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      alert(data.message || 'Mark saved');
    },
    editComponent(component) {
      this.editMode = true;
      this.editComponentData = { ...component };
    },
    cancelEdit() {
      this.editMode = false;
      this.editComponentData = { component_id: null, component_name: '', max_mark: '' };
    },
    async updateComponent() {
      try {
        const res = await fetch(`http://localhost:3000/components/${this.editComponentData.componentId}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.editComponentData)
        });
        const data = await res.json();
        alert(data.message || 'Component updated');
        this.cancelEdit();
        this.fetchComponents();
      } catch (err) {
        console.error(err);
        alert("Failed to update component");
      }
    },

    async deleteComponent(componentId) {
      if (!confirm('Are you sure you want to delete this component?')) return;

      try {
        const res = await fetch(`http://localhost:3000/components/${componentId}`, {
          method: 'DELETE'
        });
        const data = await res.json();
        alert(data.message || 'Component deleted');
        this.fetchComponents();
      } catch (err) {
        console.error(err);
        alert("Failed to delete component");
      }
    }
  },
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
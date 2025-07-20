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
    <div v-if="!editMode" class="mt-3 mb-3 d-flex flex-row gap-2 align-items-start">
      <div class="col-md-5">
        <input
          v-model="newComponent.componentName"
          :class="['form-control', newErrors.name ? 'is-invalid' : '']"
          placeholder="Component Name"
        />
        <div class="invalid-feedback" v-if="newErrors.name">{{ newErrors.name }}</div>
      </div>
      <div class="col-md-4">
        <input
          type="number"
          v-model="newComponent.maxMark"
          :class="['form-control', newErrors.mark ? 'is-invalid' : '']"
          placeholder="Max Mark"
        />
        <div class="invalid-feedback" v-if="newErrors.mark">{{ newErrors.mark }}</div>
      </div>
      <div class="col-md-3">
        <button @click="addComponent" class="btn btn-primary w-80">Add Component</button>
      </div>
    </div>

    <!-- Edit Mode -->
    <div v-if="editMode" class="mt-3 mb-3 d-flex flex-row gap-2 align-items-start">
      <div class="col-md-5">
        <input
          v-model="editComponentData.componentName"
          :class="['form-control', editErrors.name ? 'is-invalid' : '']"
        />
        <div class="invalid-feedback" v-if="editErrors.name">{{ editErrors.name }}</div>
      </div>
      <div class="col-md-4">
        <input
          v-model="editComponentData.maxMark"
          type="number"
          :class="['form-control', editErrors.mark ? 'is-invalid' : '']"
        />
        <div class="invalid-feedback" v-if="editErrors.mark">{{ editErrors.mark }}</div>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button @click="updateComponent" class="btn btn-success" style="width:40%">Update</button>
        <button @click="cancelEdit" class="btn btn-danger" style="width:40%">Cancel</button>
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
                class="btn btn-primary btn-sm"
              >
                Enter Marks
              </router-link>
              <button @click="editComponent(component)" class="btn btn-warning btn-sm">Edit</button>
              <button @click="deleteComponent(component.componentId)" class="btn btn-danger btn-sm">Delete</button>
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
      components: [],
      newComponent: { componentName: '', maxMark: '' },
      newErrors: { name: '', mark: '' },
      editMode: false,
      editComponentData: { componentId: null, componentName: '', maxMark: '' },
      editErrors: { name: '', mark: '' }
    };
  },
  computed: {
    selectedCourse() {
      return this.courses.find(course => course.sectionId === this.selectedSectionId) || null;
    },
    maxComponentMark() {
      const cm = this.selectedCourse?.maxCm;
      return cm !== null && cm !== undefined ? cm : null;
    },
    totalComponentMark() {
      return this.components.reduce((sum, c) => {
        const mark = parseFloat(c.maxMark);
        return sum + (isNaN(mark) ? 0 : mark);
      }, 0);
    }
  },
  async created() {
    await this.fetchAllLecturerCourses();
  },
  methods: {
    async fetchAllLecturerCourses() {
      try {
        if (!this.lecturerId) return;
        const res = await fetch(`http://localhost:3000/lecturer-course/${this.lecturerId}`);
        if (!res.ok) throw new Error('Failed to fetch courses');
        this.courses = await res.json();
        if (this.courses.length && !this.selectedSectionId) {
          this.selectedSectionId = this.courses[0].sectionId;
          await this.fetchComponents();
        }
      } catch (error) {
        console.error('Error fetching courses:', error);
      }
    },
    async fetchComponents() {
      try {
        const res = await fetch(`http://localhost:3000/components?section_id=${this.selectedSectionId}`);
        if (!res.ok) throw new Error('Failed to fetch components');
        this.components = await res.json();
      } catch (error) {
        console.error('Error fetching components:', error);
      }
    },
    async addComponent() {
      this.newErrors = { name: '', mark: '' };
      const name = this.newComponent.componentName.trim();
      const mark = parseFloat(this.newComponent.maxMark);

      let hasError = false;
      if (!name) {
        this.newErrors.name = 'Component name is required.';
        hasError = true;
      }
      if (isNaN(mark) || mark <= 0) {
        this.newErrors.mark = 'Enter a valid positive number.';
        hasError = true;
      }
      const maxAllowed = this.maxComponentMark;
      if (!hasError && maxAllowed !== null && this.totalComponentMark + mark > maxAllowed) {
        alert(`Total component marks cannot exceed ${maxAllowed}.`);
        return;
      }

      if (hasError) return;

      try {
        const payload = {
          sectionId: this.selectedSectionId,
          componentName: name,
          maxMark: mark
        };
        const res = await fetch('http://localhost:3000/components', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        alert(data.message || 'Component added.');
        this.newComponent.componentName = '';
        this.newComponent.maxMark = '';
        await this.fetchComponents();
      } catch (err) {
        console.error(err);
        alert('Failed to add component.');
      }
    },
    editComponent(component) {
      this.editMode = true;
      this.editErrors = { name: '', mark: '' };
      this.editComponentData = { ...component };
    },
    cancelEdit() {
      this.editMode = false;
      this.editComponentData = { componentId: null, componentName: '', maxMark: '' };
      this.editErrors = { name: '', mark: '' };
    },
    async updateComponent() {
      this.editErrors = { name: '', mark: '' };
      const name = this.editComponentData.componentName.trim();
      const mark = parseFloat(this.editComponentData.maxMark);

      let hasError = false;
      if (!name) {
        this.editErrors.name = 'Component name is required.';
        hasError = true;
      }

      if (isNaN(mark) || mark <= 0) {
        this.editErrors.mark = 'Enter a valid positive number.';
        hasError = true;
      }

      const original = this.components.find(c => c.componentId === this.editComponentData.componentId);
      const totalWithoutOriginal = this.totalComponentMark - parseFloat(original?.maxMark || 0);

      const maxAllowed = this.maxComponentMark;
      if (!hasError && maxAllowed !== null && totalWithoutOriginal + mark > maxAllowed) {
        alert(`Total component marks cannot exceed ${maxAllowed}.`);
        return;
      }

      if (hasError) return;

      try {
        const res = await fetch(`http://localhost:3000/components/${this.editComponentData.componentId}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ componentName: name, maxMark: mark })
        });
        const data = await res.json();
        alert(data.message || 'Component updated.');
        this.cancelEdit();
        await this.fetchComponents();
      } catch (err) {
        console.error(err);
        alert('Failed to update component.');
      }
    },
    async deleteComponent(componentId) {
      if (!confirm('Are you sure you want to delete this component?')) return;
      try {
        const res = await fetch(`http://localhost:3000/components/${componentId}`, {
          method: 'DELETE'
        });
        const data = await res.json();
        alert(data.message || 'Component deleted.');
        await this.fetchComponents();
      } catch (err) {
        console.error(err);
        alert('Failed to delete component.');
      }
    }
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
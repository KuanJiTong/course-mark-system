<template>
  <div class="component-marks-page">
    <div class="page-header">
      <h2>Component Marks (70%)</h2>
    </div>

    <div class="marks-table-container">
      <table class="marks-table">
        <thead>
          <tr>
            <th>Component</th>
            <th>Mark</th>
            <th>Max Mark</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(mark, index) in marks" :key="index">
            <td><input v-model="mark.component_name" class="input-field" placeholder="Enter Component" /></td>
            <td><input type="number" v-model="mark.mark" class="input-field" placeholder="Enter Mark" /></td>
            <td><input type="number" v-model="mark.max_mark" class="input-field" placeholder="Max Mark" /></td>
            <td>
              <button @click="saveMark(mark)" class="btn save-btn">💾 Save</button>
              <button @click="removeMark(index)" class="btn remove-btn">❌ Remove</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="footer-actions">
      <button @click="addRow" class="btn add-btn">➕ Add Component</button>
      <p class="total-mark">Total Marks: <strong>{{ totalPercentage }}%</strong></p>
      <p v-if="totalPercentage > 70" class="warning">⚠️ Total marks exceed 70%!</p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      marks: []
    };
  },
  computed: {
    totalPercentage() {
      return this.marks.reduce((total, m) => {
        return total + ((m.mark / m.max_mark) * 70);
      }, 0).toFixed(2);
    }
  },
  methods: {
    addRow() {
      this.marks.push({ component_name: '', mark: 0, max_mark: 100 });
    },
    saveMark(mark) {
      // send POST request to backend to save the mark
      fetch('/api/marks', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(mark)
      }).then(() => {
        alert('Saved');
      });
    },
    removeMark(index) {
      this.marks.splice(index, 1);
    }
  },
  mounted() {
    // Optionally load existing marks from backend
    fetch('/api/marks?student_id=1&course_id=1')
      .then(res => res.json())
      .then(data => {
        this.marks = data;
      });
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

.marks-table-container {
  margin-bottom: 20px;
  overflow-x: auto;
}

.marks-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

th, td {
  padding: 12px 15px;
  text-align: center;
  border: 1px solid #ddd;
}

.input-field {
  width: 100%;
  padding: 8px;
  margin: 5px;
  box-sizing: border-box;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.footer-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 10px;
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

.remove-btn {
  background-color: #f44336;
  color: white;
  margin-left: 5px;
}

.total-mark {
  font-size: 16px;
}

.warning {
  color: red;
  font-weight: bold;
}
</style>

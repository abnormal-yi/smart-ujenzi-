import React, { useState, useEffect } from 'react';
import { LucidePrinter, LucideFileText, LucidePieChart } from 'lucide-react';

export default function Reports() {
  const [projects, setProjects] = useState<any[]>([]);
  const [tasks, setTasks] = useState<any[]>([]);
  const [materials, setMaterials] = useState<any[]>([]);

  useEffect(() => {
    const fetchData = async () => {
      const token = localStorage.getItem('token');
      const headers = { 'Authorization': `Bearer ${token}` };
      
      const [projRes, tasksRes, matRes] = await Promise.all([
        fetch('/api/projects', { headers }),
        fetch('/api/tasks', { headers }),
        fetch('/api/materials', { headers })
      ]);
      
      if (projRes.ok) setProjects(await projRes.json());
      if (tasksRes.ok) setTasks(await tasksRes.json());
      if (matRes.ok) setMaterials(await matRes.json());
    };
    fetchData();
  }, []);

  const handlePrint = () => {
    window.print();
  };

  return (
    <div className="space-y-6 max-w-5xl mx-auto printable-area">
      <div className="flex justify-between items-center mb-6 no-print">
        <h2 className="text-2xl font-bold text-gray-800">System Reports</h2>
        <button 
          onClick={handlePrint}
          className="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
        >
          <LucidePrinter className="w-4 h-4 mr-2" />
          Print / Export to PDF
        </button>
      </div>

      {/* Header for Print */}
      <div className="hidden print:block text-center mb-8 border-b-2 border-slate-900 pb-4">
        <h1 className="text-3xl font-bold font-sans text-slate-900">SMART UJENZI</h1>
        <p className="text-gray-500">Construction Management System</p>
        <h2 className="text-xl font-bold mt-4">General Status Report</h2>
        <p className="text-sm text-gray-400">Generated on: {new Date().toLocaleDateString()}</p>
      </div>

      <div className="space-y-10">
        
        {/* Project Progress Report */}
        <section className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 print:shadow-none print:border-none print:p-0">
          <div className="flex items-center space-x-2 mb-4 border-b pb-2">
            <LucidePieChart className="w-5 h-5 text-blue-600 no-print" />
            <h3 className="text-lg font-bold text-gray-900">Project Progress summary</h3>
          </div>
          <table className="w-full text-left border-collapse border print:border-gray-300">
            <thead>
              <tr className="bg-gray-50 print:bg-gray-100 text-gray-700 text-sm">
                <th className="p-3 border print:border-gray-300">Project</th>
                <th className="p-3 border print:border-gray-300">Manager</th>
                <th className="p-3 border print:border-gray-300">Start Date</th>
                <th className="p-3 border print:border-gray-300">End Date</th>
                <th className="p-3 border print:border-gray-300">Status</th>
              </tr>
            </thead>
            <tbody>
              {projects.map(p => (
                <tr key={p.id} className="text-sm">
                  <td className="p-3 border print:border-gray-300 font-medium">{p.name}</td>
                  <td className="p-3 border print:border-gray-300">{p.manager_name}</td>
                  <td className="p-3 border print:border-gray-300">{p.start_date}</td>
                  <td className="p-3 border print:border-gray-300">{p.end_date}</td>
                  <td className="p-3 border print:border-gray-300">{p.status}</td>
                </tr>
              ))}
              {projects.length === 0 && <tr><td colSpan={5} className="p-4 text-center">No projects found.</td></tr>}
            </tbody>
          </table>
        </section>

        {/* Resource & Materials Usage */}
        <section className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 print:shadow-none print:border-none print:p-0">
          <div className="flex items-center space-x-2 mb-4 border-b pb-2">
            <LucideFileText className="w-5 h-5 text-green-600 no-print" />
            <h3 className="text-lg font-bold text-gray-900">Materials Inventory Status</h3>
          </div>
          <table className="w-full text-left border-collapse border print:border-gray-300">
            <thead>
              <tr className="bg-gray-50 print:bg-gray-100 text-gray-700 text-sm">
                <th className="p-3 border print:border-gray-300">Material</th>
                <th className="p-3 border print:border-gray-300">Quantity Remaining</th>
                <th className="p-3 border print:border-gray-300">Unit</th>
                <th className="p-3 border print:border-gray-300">Status</th>
              </tr>
            </thead>
            <tbody>
              {materials.map(m => {
                const isLow = m.quantity <= m.low_stock_threshold;
                return (
                  <tr key={m.id} className="text-sm">
                    <td className="p-3 border print:border-gray-300 font-medium">{m.name}</td>
                    <td className="p-3 border print:border-gray-300 font-bold">{m.quantity}</td>
                    <td className="p-3 border print:border-gray-300">{m.unit}</td>
                    <td className={`p-3 border print:border-gray-300 font-medium ${isLow ? 'text-red-600' : 'text-green-600'}`}>
                      {isLow ? 'Low Stock' : 'Sufficient'}
                    </td>
                  </tr>
                );
              })}
              {materials.length === 0 && <tr><td colSpan={4} className="p-4 text-center">No materials found.</td></tr>}
            </tbody>
          </table>
        </section>

        {/* Task Completion Summary */}
        <section className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 print:shadow-none print:border-none print:p-0">
          <div className="flex items-center space-x-2 mb-4 border-b pb-2">
            <LucideFileText className="w-5 h-5 text-yellow-600 no-print" />
            <h3 className="text-lg font-bold text-gray-900">Task Completion Overview</h3>
          </div>
          <table className="w-full text-left border-collapse border print:border-gray-300">
            <thead>
              <tr className="bg-gray-50 print:bg-gray-100 text-gray-700 text-sm">
                <th className="p-3 border print:border-gray-300">Task</th>
                <th className="p-3 border print:border-gray-300">Project</th>
                <th className="p-3 border print:border-gray-300">Supervisor</th>
                <th className="p-3 border print:border-gray-300">Status</th>
              </tr>
            </thead>
            <tbody>
              {tasks.map(t => (
                <tr key={t.id} className="text-sm">
                  <td className="p-3 border print:border-gray-300 font-medium">{t.name}</td>
                  <td className="p-3 border print:border-gray-300">{t.project_name}</td>
                  <td className="p-3 border print:border-gray-300">{t.supervisor_name}</td>
                  <td className="p-3 border print:border-gray-300">{t.status}</td>
                </tr>
              ))}
              {tasks.length === 0 && <tr><td colSpan={4} className="p-4 text-center">No tasks found.</td></tr>}
            </tbody>
          </table>
        </section>

      </div>
    </div>
  );
}

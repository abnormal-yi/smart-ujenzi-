import React from 'react';
import { useState, useEffect } from 'react';
import { LucidePlus, LucideAlertCircle } from 'lucide-react';

export default function Materials() {
  const [materials, setMaterials] = useState<any[]>([]);
  const [showModal, setShowModal] = useState(false);
  const [formData, setFormData] = useState({ name: '', quantity: 0, unit: '', low_stock_threshold: 10 });
  
  const [stockModal, setStockModal] = useState<{id: number, type: 'add'|'use', amount: number} | null>(null);

  const fetchData = async () => {
    const token = localStorage.getItem('token');
    const res = await fetch('/api/materials', { headers: { 'Authorization': `Bearer ${token}` } });
    if (res.ok) setMaterials(await res.json());
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const token = localStorage.getItem('token');
    const res = await fetch('/api/materials', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    });
    
    if (res.ok) {
      setShowModal(false);
      fetchData();
      setFormData({ name: '', quantity: 0, unit: '', low_stock_threshold: 10 });
    }
  };

  const updateStock = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!stockModal) return;
    
    const token = localStorage.getItem('token');
    const amount = stockModal.type === 'add' ? stockModal.amount : -stockModal.amount;
    
    const res = await fetch(`/api/materials/${stockModal.id}/stock`, {
      method: 'PUT',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({ amount })
    });

    if (res.ok) {
      setStockModal(null);
      fetchData();
    }
  };

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-gray-800">Materials & Resources</h2>
        <button 
          onClick={() => setShowModal(true)}
          className="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
        >
          <LucidePlus className="w-4 h-4 mr-2" />
          Add Material
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {materials.map((mat) => {
          const isLow = mat.quantity <= mat.low_stock_threshold;
          return (
            <div key={mat.id} className={`bg-white p-6 rounded-xl shadow-sm border ${isLow ? 'border-red-300' : 'border-gray-200'}`}>
              <div className="flex justify-between items-start mb-4">
                <h3 className="font-bold text-lg text-gray-900">{mat.name}</h3>
                {isLow && <LucideAlertCircle className="w-5 h-5 text-red-500" title="Low Stock" />}
              </div>
              <div className="flex items-end space-x-2 mb-6">
                <span className={`text-3xl font-bold ${isLow ? 'text-red-500' : 'text-gray-800'}`}>
                  {mat.quantity}
                </span>
                <span className="text-gray-500 mb-1">{mat.unit}</span>
              </div>
              
              <div className="flex space-x-2">
                <button 
                  onClick={() => setStockModal({ id: mat.id, type: 'add', amount: 0 })}
                  className="flex-1 bg-green-100 hover:bg-green-200 text-green-800 py-1.5 rounded text-sm font-medium transition-colors"
                >
                  + Add Stock
                </button>
                <button 
                  onClick={() => setStockModal({ id: mat.id, type: 'use', amount: 0 })}
                  className="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-800 py-1.5 rounded text-sm font-medium transition-colors"
                >
                  - Use Stock
                </button>
              </div>
            </div>
          );
        })}
      </div>
      
      {materials.length === 0 && (
        <div className="bg-white p-8 rounded-xl shadow-sm border border-gray-200 text-center text-gray-500">
          No materials tracked yet.
        </div>
      )}

      {/* Add Material Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div className="p-4 border-b border-gray-200">
              <h3 className="text-lg font-bold text-gray-900">New Material</h3>
            </div>
            <form onSubmit={handleSubmit} className="p-4 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Material Name</label>
                <input required type="text" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" placeholder="e.g. Cement" />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Initial Qty</label>
                  <input required type="number" min="0" value={formData.quantity} onChange={e => setFormData({...formData, quantity: parseInt(e.target.value)})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                  <input required type="text" value={formData.unit} onChange={e => setFormData({...formData, unit: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" placeholder="e.g. Bags, Tons" />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Low Stock Warning Threshold</label>
                <input required type="number" min="0" value={formData.low_stock_threshold} onChange={e => setFormData({...formData, low_stock_threshold: parseInt(e.target.value)})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" />
              </div>
              <div className="pt-4 flex justify-end space-x-3 border-t">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900">Add</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Update Stock Modal */}
      {stockModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden">
            <div className="p-4 border-b border-gray-200">
              <h3 className="text-lg font-bold text-gray-900">
                {stockModal.type === 'add' ? 'Add Stock' : 'Use Stock'}
              </h3>
            </div>
            <form onSubmit={updateStock} className="p-4 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                <input 
                  required 
                  type="number" 
                  min="1" 
                  value={stockModal.amount} 
                  onChange={e => setStockModal({...stockModal, amount: parseInt(e.target.value)})}
                  className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" 
                />
              </div>
              <div className="pt-4 flex justify-end space-x-3 border-t">
                <button type="button" onClick={() => setStockModal(null)} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" className={`px-4 py-2 text-white rounded-lg ${stockModal.type === 'add' ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'}`}>
                  Confirm
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

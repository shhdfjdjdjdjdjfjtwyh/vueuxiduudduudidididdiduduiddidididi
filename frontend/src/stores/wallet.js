import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/api/axios'

export const useWalletStore = defineStore('wallet', () => {
  const balance = ref(0)
  const transactions = ref([])
  const loading = ref(false)

  const fetchBalance = async () => {
    const { data } = await axios.get('/wallet/balance.php')
    if (data.success) balance.value = data.data.balance
    return data
  }

  const fetchTransactions = async (params = {}) => {
    loading.value = true
    try {
      const { data } = await axios.get('/wallet/transactions.php', { params })
      if (data.success) transactions.value = data.data.transactions
      return data
    } finally { loading.value = false }
  }

  return { balance, transactions, loading, fetchBalance, fetchTransactions }
})

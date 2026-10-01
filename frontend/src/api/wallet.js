import axios from './axios'
export const walletApi = {
  getBalance: () => axios.get('/wallet/balance.php'),
  getTransactions: (params) => axios.get('/wallet/transactions.php', { params }),
  deposit: (amount) => axios.post('/wallet/deposit.php', { amount }),
  verifyDeposit: (data) => axios.post('/wallet/verify-deposit.php', data),
  withdraw: (data) => axios.post('/wallet/withdraw.php', data)
}

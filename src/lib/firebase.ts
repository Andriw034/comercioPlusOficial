// Import the functions you need from the SDKs you need
import { initializeApp, getApps, getApp } from "firebase/app";
import { getAuth } from "firebase/auth";
import { getStorage } from "firebase/storage";
import { getFirestore } from "firebase/firestore";
import { config } from 'dotenv';

config();

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY,
  authDomain: "comercio-plus.firebaseapp.com",
  projectId: "comercio-plus",
  storageBucket: "comercio-plus.appspot.com",
  messagingSenderId: "655361811812",
  appId: "1:655361811812:web:bf1178f63ed9e8c4620671",
  measurementId: "G-8SYJPQVVZ4"
};

// Initialize Firebase
const app = !getApps().length ? initializeApp(firebaseConfig) : getApp();
const auth = getAuth(app);
const storage = getStorage(app);
const db = getFirestore(app);

export { app, db, auth, storage };

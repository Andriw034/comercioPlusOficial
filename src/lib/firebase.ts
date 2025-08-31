
// Import the functions you need from the SDKs you need
import { initializeApp, getApps, getApp } from "firebase/app";
import { getAuth, connectAuthEmulator } from "firebase/auth";
import { getStorage } from "firebase/storage";
import { initializeFirestore, connectFirestoreEmulator } from "firebase/firestore";

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
// Use initializeFirestore instead of getFirestore for better SSR compatibility
const db = initializeFirestore(app, {
  // Disable persistence on the server
  localCache: undefined,
});

// We only want to connect to the emulators in a development environment
if (process.env.NODE_ENV === 'development') {
    // Check if we're running in the browser to avoid server-side connection attempts
    if (typeof window !== "undefined") {
        try {
            connectAuthEmulator(auth, "http://127.0.0.1:9099", { disableWarnings: true });
            connectFirestoreEmulator(db, "127.0.0.1", 8080);
            console.log('Successfully connected to Firebase emulators');
        } catch (error) {
            console.error("Error connecting to Firebase emulators:", error);
        }
    }
}


export { app, db, auth, storage };

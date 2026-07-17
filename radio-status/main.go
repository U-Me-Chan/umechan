package main

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"

	"github.com/bradfitz/gomemcache/memcache"
	"github.com/trim21/go-phpserialize"
)

type TrackData struct {
	ID     int    `json:"id"`
	Artist string `json:"artist"`
	Title  string `json:"title"`
}

func main() {
	mc := memcache.New("memcached:11211")

	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		item, err := mc.Get("current_track")
		
		if err != nil {
			if err == memcache.ErrCacheMiss {
				http.Error(w, "Ключ current_track не найден", http.StatusNotFound)
				return
			}
			http.Error(w, fmt.Sprintf("Ошибка Memcached: %v", err), http.StatusInternalServerError)
			return
		}

		var rawData any

		if err := phpserialize.Unmarshal([]byte(item.Value), &rawData); err != nil {
			http.Error(w, fmt.Sprintf("Ошибка десериализации phpserialize: %v", err), http.StatusInternalServerError)
			return
		}

		var track TrackData

		if m, ok := rawData.(map[any]any); ok {
			track.ID     = int(m["id"].(int64))
			track.Artist = m["artist"].(string)
			track.Title  = m["title"].(string)
		} else {
			http.Error(w, "Неверный формат данных", http.StatusInternalServerError)
			return
		}

		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusOK)

		json.NewEncoder(w).Encode(track)
	})

	log.Println("Сервер запущен на :8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}

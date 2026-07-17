FROM golang:1.26.3-alpine AS builder
WORKDIR /app
COPY radio-status/* /app
RUN go mod download
RUN CGO_ENABLED=0 GOOS=linux go build -o server .

FROM alpine:latest
WORKDIR /root/
COPY --from=builder /app/server .
EXPOSE 8080
CMD ["./server"]
